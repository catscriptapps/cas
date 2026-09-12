// /resources/js/utils/messages/toggle-archive-message.js

import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';
import { broadcastUnreadCount } from './unread-badge.js';

/**
 * Archives (or restores) a message. Shared by the row's own archive button
 * AND the view-modal's "Archive"/"Restore to Inbox" button (see
 * view-message.js) -- both just need the encoded id and, when available,
 * the row element to swap/remove.
 */
export async function performArchiveToggle(encodedId, row) {
    if (!encodedId) return null;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ encoded_id: encodedId, action: 'toggle-archive' }),
        });
        const result = await response.json();

        if (!result.success) {
            showToast(result.messages?.[0] || 'Could not update this message.', 'error');
            return result;
        }

        if (typeof result.unreadCount === 'number') {
            broadcastUnreadCount(result.unreadCount);
        }

        showToast(result.messages?.[0] || 'Message updated.', 'success');

        // The row belongs to whichever view (Inbox/Archived) it used to be
        // in -- after a toggle it no longer matches that view's filter, so
        // it's removed from the current table rather than replaced in place
        // (unlike a normal edit, where rowHtml would just swap in).
        if (row) {
            row.style.transition = 'opacity 0.25s ease';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                updateCount('message', '#messages-tbody', '#messages-count');
                document.dispatchEvent(new CustomEvent('messages-row-removed'));
            }, 250);
        }

        return result;
    } catch (err) {
        console.error('Toggle archive error:', err);
        showToast('A network error occurred.', 'error');
        return null;
    }
}

export function initToggleArchiveMessage(tableSelector = '#messages-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.toggle-archive-message-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = btn.dataset.encodedId || row?.dataset.encodedId;
        if (!encodedId) return;

        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-wait');

        performArchiveToggle(encodedId, row);
    });
}
