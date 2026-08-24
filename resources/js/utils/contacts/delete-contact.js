// /resources/js/utils/contacts/delete-contact.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

export function initDeleteContact(tableSelector = '#contacts-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/contacts`, 'Contact');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-contact-btn');
        if (!btn) return;

        e.stopPropagation();

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;
        if (!encodedId || !row) return;

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete contact.', 'error');
                return;
            }

            showToast('Contact successfully deleted', 'success');
            updateCount('contact', tableSelector, '#contacts-count');

            const remainingRows = tbody.querySelectorAll('tr').length;
            if (remainingRows === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state-row">
                        <td colspan="100%" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <p class="font-bold text-lg font-sans">No contacts found</p>
                                <p class="text-sm font-sans">Click the "Add Contact" button to add league officials or emergency staff.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
    });
}
