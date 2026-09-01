// /resources/js/pages/my-account-page.js

import { Modal } from '../factories/modal-factory.js';
import { showToast } from '../ui/toast.js';
import { fetchCountries } from '../api/countries-api.js';
import { fetchRegions } from '../api/regions-api.js';

let modalInstance = null;

async function openEditModal(registration) {
    if (modalInstance) modalInstance.destroy();

    modalInstance = new Modal({
        id: 'edit-registration-modal',
        title: 'Edit My Info',
        content: `
            <form id="edit-registration-form" class="grid grid-cols-1 sm:grid-cols-2 gap-5 font-sans">
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Full Name</label>
                    <input type="text" name="full_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Age</label>
                    <input type="number" min="1" max="99" name="age" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Phone</label>
                    <input type="text" name="phone" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Address</label>
                    <input type="text" name="address" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">City</label>
                    <input type="text" name="city" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Postal Code</label>
                    <input type="text" name="postal_code" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Province / State</label>
                    <select id="edit-reg-country" name="countryId" class="w-full mb-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                        <option value="">Select Country</option>
                    </select>
                    <select id="edit-reg-region" name="province_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                        <option value="">Select Region</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Position</label>
                    <input type="text" name="position" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Team Name</label>
                    <input type="text" name="team_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Special Requests</label>
                    <textarea name="special_requests" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all resize-none"></textarea>
                </div>
                <div class="sm:col-span-2 api-message"></div>
                <div class="sm:col-span-2 flex justify-end pt-2">
                    <button type="submit" id="save-registration-btn"
                        class="inline-flex items-center gap-2 py-2.5 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
                        Save Changes
                    </button>
                </div>
            </form>
        `,
        size: 'lg',
        showFooter: false,
    });

    const form = document.getElementById('edit-registration-form');
    form.entry_id = registration.entry_id;
    for (const [key, value] of Object.entries(registration)) {
        const field = form.elements.namedItem(key);
        if (field && value !== null && value !== undefined) field.value = value;
    }

    await loadCountryAndRegion(registration.province_id);

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        saveRegistration(registration.entry_id, form);
    });

    modalInstance.open();
}

async function loadCountryAndRegion(currentProvinceId) {
    const countrySelect = document.getElementById('edit-reg-country');
    const regionSelect = document.getElementById('edit-reg-region');
    if (!countrySelect || !regionSelect) return;

    const countries = await fetchCountries();
    countries.forEach((c) => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        countrySelect.appendChild(opt);
    });

    const canada = countries.find((c) => /canada/i.test(c.name)) || countries[0];
    if (!canada) return;
    countrySelect.value = canada.id;

    const regions = await fetchRegions(canada.id);
    regions.forEach((r) => {
        const opt = document.createElement('option');
        opt.value = r.id;
        opt.textContent = r.name;
        if (currentProvinceId && String(r.id) === String(currentProvinceId)) opt.selected = true;
        regionSelect.appendChild(opt);
    });

    countrySelect.addEventListener('change', async () => {
        const regions = await fetchRegions(countrySelect.value);
        regionSelect.innerHTML = '<option value="">Select Region</option>';
        regions.forEach((r) => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.name;
            regionSelect.appendChild(opt);
        });
    });
}

async function saveRegistration(entryId, form) {
    const btn = document.getElementById('save-registration-btn');
    const apiMsg = document.querySelector('#edit-registration-modal .api-message');
    if (!btn) return;

    btn.disabled = true;
    const originalLabel = btn.innerHTML;
    btn.innerHTML = 'Saving...';
    if (apiMsg) apiMsg.innerHTML = '';

    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    payload.entry_id = entryId;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/my-account-update`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();

        if (result.success) {
            showToast('Your information has been updated.', 'success');
            setTimeout(() => modalInstance?.close(), 400);
            // Simplest reliable way to reflect the change in every card field
            // (payment/team sections aren't editable, so nothing there is lost).
            setTimeout(() => window.location.reload(), 600);
        } else if (apiMsg) {
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm">${result.messages?.[0] || 'Save failed.'}</div>`;
        }
    } catch (err) {
        console.error('My-account save error:', err);
        if (apiMsg) {
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm">Unexpected error. Please try again.</div>`;
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalLabel;
    }
}

let listenersAttached = false;

export function init() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-edit-registration-btn]');
        if (!btn) return;
        e.preventDefault();

        const card = btn.closest('[data-registration-card]');
        if (!card) return;

        try {
            const registration = JSON.parse(card.dataset.registration);
            openEditModal(registration);
        } catch (err) {
            console.error('Could not parse registration data:', err);
        }
    });

    listenersAttached = true;
}
