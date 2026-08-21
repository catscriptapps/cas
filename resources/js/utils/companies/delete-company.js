// /resources/js/utils/companies/delete-company.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Attaches delete functionality to the companies table via delegation.
 */
export function initDeleteCompany(tableSelector = '#companies-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/companies`, 'Company');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-company-btn');
        if (!btn) return;

        e.stopPropagation();

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;

        if (!encodedId || !row) {
            console.error('Delete failed: Missing encoded ID or row element.');
            return;
        }

        deleteHandler.showConfirmation(encodedId, row, (success) => {
            if (!success) return;

            showToast('Company successfully deleted', 'success');
            updateCount('company', tableSelector, '#companies-count');

            const remainingRows = tbody.querySelectorAll('tr').length;
            if (remainingRows === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state-row">
                        <td colspan="100%" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-2">🏢</span>
                                <p class="font-bold text-lg">No companies found</p>
                                <p class="text-sm">Click the "Add Company" button to onboard one.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
    });
}
