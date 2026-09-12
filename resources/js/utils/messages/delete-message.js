// /resources/js/utils/messages/delete-message.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';
import { broadcastUnreadCount } from './unread-badge.js';

let deleteHandler = null;

function getDeleteHandler() {
    if (!deleteHandler) {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        deleteHandler = createDeleteHandler(`${baseUrl}api/messages`, 'Message');
    }
    return deleteHandler;
}

/**
 * Shared by the row's own delete button AND the view-modal's "Delete"
 * button (see view-message.js) -- both just need the encoded id and the row
 * element (found by id if not already on hand, e.g. from the modal).
 */
export function confirmDeleteMessage(encodedId, row) {
    if (!encodedId || !row) return;

    getDeleteHandler().showConfirmation(encodedId, row, (result) => {
        if (result?.success === false) {
            showToast(result.messages?.[0] || 'Could not delete message.', 'error');
            return;
        }

        if (typeof result?.unreadCount === 'number') {
            broadcastUnreadCount(result.unreadCount);
        }

        showToast('Message deleted.', 'success');
        updateCount('message', '#messages-tbody', '#messages-count');

        const tbody = document.getElementById('messages-tbody');
        if (tbody && tbody.querySelectorAll('tr').length === 0) {
            tbody.innerHTML = `
                <tr class="empty-state-row">
                    <td colspan="100%" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center">
                            <p class="font-bold text-lg font-sans">No messages</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    });
}

export function initDeleteMessage(tableSelector = '#messages-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-message-btn');
        if (!btn) return;

        e.stopPropagation();

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = btn.dataset.encodedId || row?.dataset.encodedId;
        confirmDeleteMessage(encodedId, row);
    });
}
