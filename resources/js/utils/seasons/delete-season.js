// /resources/js/utils/seasons/delete-season.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

export function initDeleteSeason(tableSelector = '#seasons-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/seasons`, 'Season Entry');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-season-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;

        if (!encodedId || !row) {
            console.error('Delete failed: Missing data-encoded-id on row.');
            return;
        }

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete season entry.', 'error');
                return;
            }

            showToast('Season entry successfully removed', 'success');
            updateCount('season', tableSelector, '#seasons-count');

            const remainingRows = tbody.querySelectorAll('tr:not(.empty-state-row)').length;
            if (remainingRows === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state-row">
                        <td colspan="100%" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <p class="font-bold text-lg font-sans">No seasons scheduled</p>
                                <p class="text-sm font-sans">Select a division and year above to create a new season.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
    });
}
