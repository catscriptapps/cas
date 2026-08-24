// /resources/js/utils/registrations/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { updateCount } from '../../components/table-pagination-count.js';

function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    return {
        encoded_id: form.dataset.encodedId || null,
        full_name: data.fullName?.trim(),
        email: data.email?.trim(),
        phone: data.phone?.trim(),
        city: data.city?.trim(),
        position: data.position?.trim(),
        team_name: data.teamName?.trim(),
        has_paid: form.querySelector('input[name="hasPaid"]')?.checked ? 1 : 0,
        status_id: form.querySelector('input[name="isActive"]')?.checked ? 1 : 0,
        _method: 'PUT',
    };
}

export function handleRegistrationFormSubmission(form, modalInstance, tableSelector = '#registrations-tbody') {
    if (form._registrationFormListenerAttached) return;
    form._registrationFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    let apiMsg = form.querySelector('.api-message') || (() => {
        const div = document.createElement('div');
        div.className = 'api-message mt-4 transition-all duration-300';
        form.appendChild(div);
        return div;
    })();

    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';

            const response = await fetch(`${baseUrl}api/registrations`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const tbody = document.querySelector(tableSelector);
                if (tbody && result.rowHtml) {
                    const existingRow = document.querySelector(`tr[data-encoded-id="${payload.encoded_id}"]`);
                    if (existingRow) existingRow.outerHTML = result.rowHtml;
                    updateCount('registration', tableSelector, '#registrations-count');
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Saved successfully.'}
                    </div>
                `;

                submitBtn.style.visibility = 'hidden';
                setTimeout(() => modalInstance?.close(), 800);
            } else {
                apiMsg.innerHTML = (result.messages || ['Error']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>
                `).join('');
            }
        } catch (err) {
            console.error('Submission Error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Unexpected error.</div>`;
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
