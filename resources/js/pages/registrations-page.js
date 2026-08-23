// /resources/js/pages/registrations-page.js

import { initRegistrationsModal } from '../modals/registrations-modal.js';
import { initDeleteRegistration } from '../utils/registrations/delete-registration.js';
import { initViewRegistration } from '../utils/registrations/view-registration.js';
import { initDataTable } from '../components/data-table.js';

/**
 * Initialize the Registrations admin page JS. No "add" button -- creation is
 * public-only, via the /register wizard.
 */
export function init() {
    initRegistrationsModal();
    initDeleteRegistration();
    initViewRegistration();

    initDataTable({
        tbodyId: 'registrations-tbody',
        countId: 'registrations-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/registrations`,
        resourceLabel: 'registration',
        colspan: 7,
        defaultSort: 'joined',
        defaultDir: 'desc',
    });
}
