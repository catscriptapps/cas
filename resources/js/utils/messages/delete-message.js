// /resources/js/utils/messages/delete-message.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';
import { showEmptyState } from './message-actions.js';

/**
 * Attaches custom factory delete functionality to the messages list via delegation.
 */
export function initDeleteMessage(containerSelector = '#messages-tbody') {
    const tbody = document.querySelector(containerSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    // Initialize factory for Messages
    const deleteHandler = createDeleteHandler(`${baseUrl}api/messages`, 'Message');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-message-btn');
        if (!btn) return;

        e.stopPropagation();

        // Match the data attribute used in your table-row.php
        const row = btn.closest('[data-messages-id]');
        const id = row?.dataset.messagesId;

        if (!id || !row) {
            console.error('Delete failed: Missing ID or row element.');
            return;
        }

        // Trigger the factory's sleek confirmation modal
        deleteHandler.showConfirmation(id, row, (success) => {
            if (!success) return;

            showToast('Message successfully deleted', 'success');

            // Update footer count
            updateCount('message', containerSelector, '#messages-count');

            // Handle empty state transition and sidebar sync
            const remainingRows = tbody.querySelectorAll('[data-messages-id]').length;
            if (remainingRows === 0) {
                const container = document.querySelector('#messages-container');
                const folderName = document.querySelector('#current-folder-name')?.textContent || 'Inbox';
                
                // 1. Show the empty state UI
                showEmptyState(container, folderName);

                // 2. Hide the "New" badge in the sidebar if it exists
                const unreadBadge = document.querySelector('#unread-count-badge');
                if (unreadBadge) {
                    unreadBadge.classList.add('hidden');
                }
            }
        });
    });
}