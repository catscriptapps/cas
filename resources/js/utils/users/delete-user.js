// /resources/js/utils/users/delete-user.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Attaches delete functionality to the users table via delegation.
 */
export function initDeleteUser(tableSelector = '#users-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    // Initialize the factory pointing to the users API
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/users`, 'User');

    // Delegate click to delete buttons
    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-user-btn');
        if (!btn) return;

        e.stopPropagation(); // Prevent row click navigation

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;

        if (!encodedId || !row) {
            console.error('Delete failed: Missing encoded ID or row element.');
            return;
        }

        // Trigger the factory's confirmation modal
        deleteHandler.showConfirmation(encodedId, row, (success) => {
            if (!success) return;

            // 1. Show high-contrast toast
            showToast('User account successfully deleted', 'success');

            // 2. Update the count in the footer (User context)
            updateCount('user', tableSelector, '#users-count');

            // 3. Handle empty state if no rows are left
            const remainingRows = tbody.querySelectorAll('tr').length;
            if (remainingRows === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state-row">
                        <td colspan="100%" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-2">👥</span>
                                <p class="font-bold text-lg">No users found</p>
                                <p class="text-sm">Click the "Add User" button to create an account.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
    });
}