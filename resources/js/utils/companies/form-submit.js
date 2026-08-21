// /resources/js/utils/companies/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Maps form data to an API payload for Companies
 */
function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    return {
        encoded_id: form.dataset.encodedId || null,
        company_name: data.companyName?.trim(),
        email: data.email?.trim(),
        phone: data.phone?.trim(),
        toll_free: data.tollFree?.trim(),
        website: data.website?.trim(),
        slogan: data.slogan?.trim(),
        address: data.address?.trim(),
        city: data.city?.trim(),
        postal_code: data.postalCode?.trim(),
        general_summary: data.generalSummary?.trim(),
        country_id: parseInt(data.countryId),
        region_id: parseInt(data.regionId),
        status_id: form.querySelector('input[name="isActive"]')?.checked ? 1 : 0,
    };
}

export function handleCompanyFormSubmission(form, mode, modalInstance, tableSelector = '#companies-tbody') {
    if (form._companyFormListenerAttached) return;
    form._companyFormListenerAttached = true;

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
            if (mode === 'edit') payload._method = 'PUT';

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/companies`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const tbody = document.querySelector(tableSelector);
                if (tbody) {
                    if (mode === 'edit' && result.rowHtml) {
                        const existingRow = document.getElementById(`company-row-${result.data?.id}`) ||
                                           document.querySelector(`tr[data-encoded-id="${payload.encoded_id}"]`);
                        if (existingRow) existingRow.outerHTML = result.rowHtml;
                    } else if (result.rowHtml) {
                        const placeholder = tbody.querySelector('td[colspan]');
                        if (placeholder) placeholder.closest('tr')?.remove();

                        tbody.insertAdjacentHTML('afterbegin', result.rowHtml);
                    }
                    updateCount('company', tableSelector, '#companies-count');
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Saved successfully.'}
                    </div>
                `;

                submitBtn.style.visibility = 'hidden';
                setTimeout(() => modalInstance?.close(), (mode === 'add') ? 1500 : 800);

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
