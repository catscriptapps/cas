// /resources/js/pages/users-page.js

import { initUsersModal } from '../modals/users-modal.js';
import { initDeleteUser } from '../utils/users/delete-user.js';
import { initViewUser } from '../utils/users/view-user.js';
import { initDataTable } from '../components/data-table.js';

/**
 * Initialize the Users page JS.
 */
export function init() {
    initUsersModal();
    initDeleteUser();
    initViewUser();

    // Sticky header + per-column filters + filter-safe infinite scroll --
    // see resources/js/components/data-table.js for the shared engine.
    initDataTable({
        tbodyId: 'users-tbody',
        countId: 'users-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/users`,
        resourceLabel: 'user',
        colspan: 6,
        defaultSort: 'joined',
        defaultDir: 'desc',
    });
}
