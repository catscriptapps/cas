// /resources/js/utils/incident-reports/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { updateCount } from '../../components/table-pagination-count.js';

function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    return {
        encoded_id: form.dataset.encodedId || null,
        incident_date: data.incident_date,
        incident_time: data.incident_time,
        location: data.location?.trim(),
        teams_involved: data.teams_involved?.trim(),
        persons_involved: data.persons_involved?.trim(),
        ref_involved: data.ref_involved?.trim(),
        timekeeper: data.timekeeper?.trim(),
        incident: data.incident?.trim(),
        equipment_worn: data.equipment_worn?.trim(),
        medical_assistance: data.medical_assistance?.trim(),
        manager_name: data.manager_name?.trim(),
        manager_time: data.manager_time,
        referee_outcome: data.referee_outcome?.trim(),
        name_e_signature: data.name_e_signature?.trim(),
        status_id: form.querySelector('input[name="status_id"]')?.checked ? 1 : 0,
    };
}

export function handleIncidentReportFormSubmission(
    form,
    mode,
    modalInstance,
    tableSelector = '#incidents-tbody'
) {
    if (form._incidentFormListenerAttached) return;
    form._incidentFormListenerAttached = true;

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
            const response = await fetch(`${baseUrl}api/incident-reports`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const tbody = document.querySelector(tableSelector);

                if (tbody) {
                    const emptyStateRow = tbody.querySelector('.empty-state-row') ||
                        tbody.querySelector('td[colspan]')?.closest('tr');
                    if (emptyStateRow) emptyStateRow.remove();

                    if (mode === 'edit' && result.rowHtml) {
                        const existingRow = tbody.querySelector(`tr[data-encoded-id="${payload.encoded_id}"]`);
                        if (existingRow) {
                            existingRow.outerHTML = result.rowHtml;
                        }
                    } else if (result.rowHtml) {
                        tbody.insertAdjacentHTML('afterbegin', result.rowHtml);
                    }

                    updateCount('incident', tableSelector, '#incidents-count');
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Incident report saved successfully.'}
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
