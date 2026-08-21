// /resources/js/utils/messages/message-actions.js

import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

export function initMessageActions(mainContainer) {
    if (!mainContainer || mainContainer.dataset.actionsBound) return; 
    
    // Mark as bound so we don't attach multiple listeners if init is called twice
    mainContainer.dataset.actionsBound = "true";

    mainContainer.addEventListener('click', async (e) => {
        // Find the closest button that has a data-action attribute
        const actionBtn = e.target.closest('button[data-action]');
        if (!actionBtn) return;

        // CRITICAL: Stop the event from bubbling up to the row (which opens the drawer)
        e.preventDefault();
        e.stopPropagation();

        const action = actionBtn.dataset.action; // 'archive' or 'delete'
        const row = actionBtn.closest('[data-messages-id]');
        const id = actionBtn.dataset.id || row?.dataset.messagesId;

        if (action === 'archive') {
            performAction(id, 'archive', row, window.APP_CONFIG?.baseUrl ?? '');
        }
    });
}

/**
 * Shared helper to talk to the API and animate the row out
 */
async function performAction(id, actionType, row, baseUrl) {
    try {
        const response = await fetch(`${baseUrl}api/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: actionType, 
                id: id 
            })
        });

        const result = await response.json();

        if (result.success) {
            row.style.transition = 'all 0.3s ease';
            row.classList.add('opacity-0', 'translate-x-4');
            
            setTimeout(() => {
                const parent = row.parentElement;
                row.remove();

                // Check if there are any rows left in the current view
                const remainingRows = document.querySelectorAll('[data-messages-id]');
                
                if (remainingRows.length === 0) {
                    const currentFolderName = document.querySelector('#current-folder-name')?.textContent || 'Inbox';
                    showEmptyState(document.querySelector('#messages-container'), currentFolderName);
                    
                    // Hide the "New" badge because the unread folder is now empty
                    const unreadBadge = document.querySelector('#unread-count-badge');
                    if (unreadBadge) {
                        unreadBadge.classList.add('hidden');
                    }
                } else {
                    updateCount('message', '#messages-tbody', '#messages-count');
                }
                
                showToast(`Message ${actionType}d successfully.`, 'success');
            }, 300);
        } else {
            showToast(result.message || `Failed to ${actionType} message.`, 'error');
        }
    } catch (error) {
        console.error(`${actionType} failed:`, error);
        showToast('Network error. Please try again.', 'error');
    }
}

export function showEmptyState(container, folderName) {
    container.innerHTML = `
        <div class="empty-state flex flex-col items-center justify-center py-24 text-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Empty Folder</h3>
            <p class="text-sm text-gray-500 max-w-xs mx-auto">Looks like there are no messages in your ${folderName} at the moment.</p>
        </div>
    `;
}