// /resources/js/utils/incident-reports/delete-incident-report.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

export function initDeleteIncidentReport(tableSelector = '#incidents-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/incident-reports`, 'Incident Report');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-report-btn');
        if (!btn) return;

        e.stopPropagation();

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;
        if (!encodedId || !row) return;

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete incident report.', 'error');
                return;
            }

            showToast('Incident report successfully deleted', 'success');
            updateCount('incident', tableSelector, '#incidents-count');

            const remainingRows = tbody.querySelectorAll('tr').length;
            if (remainingRows === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state-row">
                        <td colspan="100%" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <p class="font-bold text-lg font-sans">No incident reports found</p>
                                <p class="text-sm font-sans">Click the "File Report" button to log a new incident.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
    });
}
