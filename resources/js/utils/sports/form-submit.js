// /resources/js/utils/sports/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';

export function handleSportFormSubmission(form, modalInstance, tableSelector = '#sports-tbody') {
    if (form._sportFormListenerAttached) return;
    form._sportFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalLabel = submitBtn.innerHTML;

    let apiMsg = form.querySelector('.api-message') || (() => {
        const div = document.createElement('div');
        div.className = 'api-message mt-4';
        form.appendChild(div);
        return div;
    })();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const payload = {
                encoded_id: form.dataset.encodedId || null,
                sport_name: data.sport_name?.trim(),
                status_id: form.querySelector('input[name="isActive"]')?.checked ? 1 : 0,
            };

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/sports`, {
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
                    else tbody.insertAdjacentHTML('afterbegin', result.rowHtml);
                }

                apiMsg.innerHTML = `<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">${result.messages?.[0] || 'Saved successfully.'}</div>`;
                submitBtn.style.visibility = 'hidden';
                setTimeout(() => modalInstance?.close(), 800);
            } else {
                apiMsg.innerHTML = (result.messages || ['Error']).map(msg => `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>`).join('');
            }
        } catch (err) {
            console.error('Sport save error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Unexpected error.</div>`;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;
        }
    });
}
