// /resources/js/pages/register-page.js

import { fetchSports, fetchLeagues, fetchDivisions, fetchSources } from '../api/divisions-api.js';
import { fetchCountries } from '../api/countries-api.js';
import { fetchRegions } from '../api/regions-api.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { attachPostalFormatter } from '../utils/postal-formatter.js';
import { FormValidator } from '../utils/form-validator.js';
import { showToast } from '../ui/toast.js';

const state = {
    sportId: null,
    leagueId: null,
    divisionId: null,
    divisionLabel: '',
    amountDue: 0,
    encodedId: null,
    fullName: '',
};

function goToStep(step) {
    document.querySelectorAll('.register-step').forEach((el) => {
        el.classList.toggle('hidden', el.dataset.step !== String(step));
    });
    document.querySelectorAll('[data-step-dot]').forEach((dot) => {
        const isDone = parseInt(dot.dataset.stepDot, 10) <= step;
        dot.classList.toggle('bg-primary-500', isDone);
        dot.classList.toggle('bg-gray-200', !isDone);
        dot.classList.toggle('dark:bg-gray-700', !isDone);
    });
    const wizard = document.getElementById('register-wizard');
    if (wizard) wizard.dataset.currentStep = String(step);
    wizard?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function tileButton(label, sublabel, onClick) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'group text-left p-5 rounded-2xl border-2 border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-primary-400 hover:shadow-lg transition-all';
    btn.innerHTML = `
        <span class="block text-sm font-black text-secondary-900 dark:text-white group-hover:text-primary-600 transition-colors">${label}</span>
        ${sublabel ? `<span class="block text-xs text-gray-400 font-bold mt-1">${sublabel}</span>` : ''}
    `;
    btn.addEventListener('click', onClick);
    return btn;
}

async function loadSports(preselectSportId = null) {
    const container = document.getElementById('register-sports');
    if (!container) return;
    container.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-400">Loading…</p>';

    const sports = await fetchSports();
    container.innerHTML = '';

    if (!sports.length) {
        container.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-400">No sports available right now.</p>';
        return;
    }

    sports.forEach((sport) => {
        container.appendChild(tileButton(sport.name, null, () => selectSport(sport)));
    });

    const match = preselectSportId ? sports.find((s) => String(s.id) === String(preselectSportId)) : null;
    if (match) selectSport(match);
}

async function selectSport(sport) {
    state.sportId = sport.id;
    await loadLeagues(sport.id);
    goToStep(2);
}

async function loadLeagues(sportId) {
    const container = document.getElementById('register-leagues');
    if (!container) return;
    container.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-400">Loading…</p>';

    const leagues = await fetchLeagues(sportId);
    container.innerHTML = '';

    if (!leagues.length) {
        container.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-400">No leagues available for this sport yet.</p>';
        return;
    }

    leagues.forEach((league) => {
        container.appendChild(tileButton(league.name, null, () => selectLeague(league)));
    });
}

async function selectLeague(league) {
    state.leagueId = league.id;
    await loadDivisions(league.id);
    goToStep(3);
}

async function loadDivisions(leagueId) {
    const container = document.getElementById('register-divisions');
    if (!container) return;
    container.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-400">Loading…</p>';

    const divisions = await fetchDivisions(leagueId);
    container.innerHTML = '';

    if (!divisions.length) {
        container.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-400">No divisions available for this league yet.</p>';
        return;
    }

    divisions.forEach((division) => {
        container.appendChild(tileButton(division.name, `$${division.price.toFixed(2)}`, () => selectDivision(division)));
    });
}

function selectDivision(division) {
    state.divisionId = division.id;
    state.divisionLabel = division.name;
    state.amountDue = division.price;
    goToStep(4);
}

async function loadSourceOptions() {
    const select = document.getElementById('reg-source');
    if (!select) return;
    const sources = await fetchSources();
    sources.forEach((s) => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name;
        select.appendChild(opt);
    });
}

async function loadCountryOptions() {
    const select = document.getElementById('reg-country');
    if (!select) return;
    const countries = await fetchCountries();
    countries.forEach((c) => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        select.appendChild(opt);
    });

    // Default to Canada if present, and preload its regions.
    const canada = countries.find((c) => /canada/i.test(c.name));
    if (canada) {
        select.value = canada.id;
        const regions = await fetchRegions(canada.id);
        const regionSelect = document.getElementById('reg-region');
        if (regionSelect) {
            regions.forEach((r) => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.name;
                regionSelect.appendChild(opt);
            });
        }
    }
}

function wireDetailsForm() {
    const form = document.getElementById('register-details-form');
    if (!form) return;
    const validator = new FormValidator(form);

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!validator.validateForEmptyFields(e)) return;
        goToStep(5);
    });

    document.querySelectorAll('[data-back-step]').forEach((btn) => {
        btn.addEventListener('click', () => goToStep(parseInt(btn.dataset.backStep, 10)));
    });

    enableDynamicRegionLoading('register-details-form');
    attachPostalFormatter('register-details-form');
}

function wireWaiverStep() {
    const checkbox = document.getElementById('register-agree-checkbox');
    const submitBtn = document.getElementById('register-submit-btn');
    if (!checkbox || !submitBtn) return;

    checkbox.addEventListener('change', () => {
        submitBtn.disabled = !checkbox.checked;
    });

    submitBtn.addEventListener('click', submitRegistration);
}

async function submitRegistration() {
    const submitBtn = document.getElementById('register-submit-btn');
    const errorBox = document.getElementById('register-submit-error');
    const form = document.getElementById('register-details-form');
    if (!form) return;

    const formData = new FormData(form);
    const details = Object.fromEntries(formData.entries());

    const payload = {
        division_id: state.divisionId,
        first_name: details.first_name?.trim(),
        last_name: details.last_name?.trim(),
        age: details.age,
        email: details.email?.trim(),
        phone: details.phone?.trim(),
        address: details.address?.trim(),
        city: details.city?.trim(),
        region_id: details.regionId,
        postal_code: details.postalCode?.trim(),
        desired_position: details.desired_position?.trim(),
        hear_about_us: details.hear_about_us || null,
        team_name: details.team_name?.trim() || null,
        special_requests: details.special_requests?.trim() || null,
    };

    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting…';
    errorBox.classList.add('hidden');

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const res = await fetch(`${baseUrl}api/register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await res.json();

        if (!result.success) {
            throw new Error(result.messages?.[0] || 'Something went wrong.');
        }

        state.encodedId = result.encoded_id;
        state.amountDue = result.amount_due;
        state.divisionLabel = result.division_label;
        state.fullName = `${payload.first_name} ${payload.last_name}`;

        renderPaymentStep();
        goToStep(6);
    } catch (err) {
        errorBox.textContent = err.message;
        errorBox.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Continue to Payment';
    }
}

function renderPaymentStep() {
    document.getElementById('register-division-label').textContent = state.divisionLabel || '';
    document.getElementById('register-amount-due').textContent = state.amountDue.toFixed(2);
    document.getElementById('register-currency-label').textContent = window.APP_CONFIG?.paypalCurrency || 'CAD';

    const clientId = window.APP_CONFIG?.paypalClientId;
    if (!clientId) {
        document.getElementById('paypal-unconfigured-notice')?.classList.remove('hidden');
        return;
    }

    loadPayPalSdk(clientId).then(renderPayPalButtons).catch(() => {
        showToast('Could not load PayPal. Please try again shortly.', 'error');
    });
}

let paypalSdkPromise = null;
function loadPayPalSdk(clientId) {
    if (window.paypal) return Promise.resolve();
    if (paypalSdkPromise) return paypalSdkPromise;

    paypalSdkPromise = new Promise((resolve, reject) => {
        const currency = window.APP_CONFIG?.paypalCurrency || 'CAD';
        const script = document.createElement('script');
        script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(clientId)}&currency=${encodeURIComponent(currency)}`;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });

    return paypalSdkPromise;
}

function renderPayPalButtons() {
    const container = document.getElementById('paypal-buttons');
    if (!container || !window.paypal) return;
    container.innerHTML = '';

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const errorBox = document.getElementById('register-payment-error');

    window.paypal.Buttons({
        createOrder: async () => {
            const res = await fetch(`${baseUrl}api/paypal-create-order`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ encoded_id: state.encodedId }),
            });
            const result = await res.json();
            if (!result.success) throw new Error(result.messages?.[0] || 'Could not start payment.');
            return result.order_id;
        },
        onApprove: async (data) => {
            const res = await fetch(`${baseUrl}api/paypal-capture-order`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: data.orderID }),
            });
            const result = await res.json();
            if (!result.success) throw new Error(result.messages?.[0] || 'Payment could not be confirmed.');

            document.getElementById('register-confirm-name').textContent = state.fullName;
            goToStep(7);
        },
        onCancel: () => {
            showToast('Payment cancelled.', 'error');
        },
        onError: (err) => {
            console.error('PayPal error:', err);
            if (errorBox) {
                errorBox.textContent = 'Something went wrong with PayPal. Please try again.';
                errorBox.classList.remove('hidden');
            }
        },
    }).render('#paypal-buttons');
}

export function init() {
    const params = new URLSearchParams(window.location.search);
    const preselectSportId = params.get('sport_id');

    loadSports(preselectSportId);
    loadCountryOptions();
    loadSourceOptions();
    wireDetailsForm();
    wireWaiverStep();
}
