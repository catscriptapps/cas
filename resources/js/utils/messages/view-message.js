// /resources/js/utils/messages/view-message.js

import { showToast } from '../../ui/toast.js';
import { broadcastUnreadCount } from './unread-badge.js';
import { performArchiveToggle } from './toggle-archive-message.js';
import { confirmDeleteMessage } from './delete-message.js';

let currentEncodedId = null;

/**
 * Marks a message read the moment its row is opened -- "viewing" a message
 * IS "reading" it here, there's no separate "mark as read" affordance
 * elsewhere. Swaps the row in place (rowHtml reflects the new read state:
 * no more unread dot/bold text/"Unread" badge) and pushes the new unread
 * total to the topbar badge.
 */
async function markRead(encodedId, row) {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ encoded_id: encodedId, action: 'mark-read' }),
        });
        const result = await response.json();

        if (!result.success) return;

        if (row && result.rowHtml) {
            row.outerHTML = result.rowHtml;
        }
        if (typeof result.unreadCount === 'number') {
            broadcastUnreadCount(result.unreadCount);
        }
    } catch (err) {
        console.error('Mark message read error:', err);
    }
}

export function initViewMessage() {
    const modal = document.getElementById('view-message-modal');
    if (!modal) return;

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-message-trigger');
        if (!trigger) return;

        const data = trigger.dataset;
        currentEncodedId = data.encodedId;

        document.getElementById('view-message-subject').textContent = data.subject || '(No subject)';
        document.getElementById('view-message-meta').textContent = `${data.fullName || 'Unknown'} • ${data.email || ''}`;
        document.getElementById('view-message-date').textContent = data.dateFormatted || 'N/A';
        document.getElementById('view-message-body').textContent = data.message || '';

        const archiveBtn = document.getElementById('view-message-archive-btn');
        if (archiveBtn) archiveBtn.textContent = data.isArchived === '1' ? 'Restore to Inbox' : 'Archive';

        modal.classList.remove('hidden');

        if (data.isRead === '0') {
            const row = trigger.closest('tr[data-encoded-id]');
            markRead(data.encodedId, row);
        }
    });

    const closeModal = () => {
        modal.classList.add('hidden');
        currentEncodedId = null;
    };

    document.addEventListener('click', (e) => {
        if (e.target.closest('.close-view-message') || e.target.id === 'close-view-message-overlay') {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    document.getElementById('view-message-archive-btn')?.addEventListener('click', () => {
        if (!currentEncodedId) return;
        // Captured before closeModal() runs -- it nulls the module-level
        // currentEncodedId, so reading it after that call (instead of
        // capturing it first) silently passed null through here.
        const encodedId = currentEncodedId;
        const row = document.querySelector(`#messages-tbody tr[data-encoded-id="${encodedId}"]`);
        closeModal();
        performArchiveToggle(encodedId, row);
    });

    document.getElementById('view-message-delete-btn')?.addEventListener('click', () => {
        if (!currentEncodedId) return;
        const encodedId = currentEncodedId;
        const row = document.querySelector(`#messages-tbody tr[data-encoded-id="${encodedId}"]`);
        if (!row) {
            showToast('Could not find this message in the current list.', 'error');
            return;
        }
        closeModal();
        confirmDeleteMessage(encodedId, row);
    });
}
