// /resources/js/utils/registrations/delete-registration.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

export function initDeleteRegistration(tableSelector = '#registrations-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/registrations`, 'Registration');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-registration-btn');
        if (!btn) return;

        e.stopPropagation();

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;
        if (!encodedId || !row) return;

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete registration.', 'error');
                return;
            }

            showToast('Registration deleted successfully', 'success');
            updateCount('registration', tableSelector, '#registrations-count');

            const remainingRows = tbody.querySelectorAll('tr').length;
            if (remainingRows === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state-row">
                        <td colspan="100%" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <p class="font-bold text-lg">No registrations yet</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
    });
}
