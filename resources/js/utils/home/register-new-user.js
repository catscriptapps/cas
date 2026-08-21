// /resources/js/utils/home/register-new-user.js

import { openAddUserModal } from '../../modals/users-modal.js';

/**
 * Initialize the registration trigger for home page buttons.
 */
export function initRegisterNewUser() {
    document.addEventListener('click', (e) => {

        // Look for the specific button class
        const registerBtn = e.target.closest('.register-user-btn');

        if (registerBtn) {
            e.preventDefault();
            e.stopImmediatePropagation();

            // Trigger the existing logic from our users modal
            openAddUserModal();
        }
    });
}