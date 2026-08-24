// /resources/js/utils/contacts/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { updateCount } from '../../components/table-pagination-count.js';

function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    return {
        encoded_id: form.dataset.encodedId || null,
        full_name: data.full_name?.trim(),
        organization: data.organization?.trim(),
        email: data.email?.trim(),
        phone: data.phone?.trim(),
        leagues: data.leagues?.trim() || 'All',
        role_id: parseInt(data.role_id, 10),
        is_emergency: form.querySelector('input[name="is_emergency"]')?.checked ? 1 : 0,
    };
}

export function handleContactFormSubmission(
    form,
    mode,
    modalInstance,
    tableSelector = '#contacts-tbody'
) {
    if (form._contactFormListenerAttached) return;
    form._contactFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    let apiMsg = form.querySelector('.api-message');

    if (!apiMsg) {
        apiMsg = document.createElement('div');
        apiMsg.className = 'api-message mt-4 transition-all duration-300';
        form.appendChild(apiMsg);
    }

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
            const response = await fetch(`${baseUrl}api/contacts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const tbody = document.querySelector(tableSelector);

                if (tbody) {
                    const emptyStateRow = tbody.querySelector('td[colspan]')?.closest('tr');
                    if (emptyStateRow) {
                        emptyStateRow.remove();
                    }

                    if (mode === 'edit' && result.rowHtml) {
                        const existingRow = tbody.querySelector(`tr[data-encoded-id="${payload.encoded_id}"]`);
                        if (existingRow) {
                            existingRow.outerHTML = result.rowHtml;
                        }
                    } else if (result.rowHtml) {
                        tbody.insertAdjacentHTML('afterbegin', result.rowHtml);
                    }

                    updateCount('contact', tableSelector, '#contacts-count');
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Contact saved successfully.'}
                    </div>
                `;

                submitBtn.style.display = 'none';

                setTimeout(() => {
                    if (modalInstance && typeof modalInstance.close === 'function') {
                        modalInstance.close();
                    }
                }, 800);
            } else {
                apiMsg.innerHTML = (result.messages || ['Check your input']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${msg}
                    </div>
                `).join('');
            }
        } catch (err) {
            console.error('Submission Error:', err);
            apiMsg.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                    Unexpected error. Please try again.
                </div>`;
        } finally {
            if (submitBtn.style.display !== 'none') {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
