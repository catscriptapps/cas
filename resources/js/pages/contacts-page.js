// /resources/js/pages/contacts-page.js

import { initContactsModal } from '../modals/contacts-modal.js';
import { initDeleteContact } from '../utils/contacts/delete-contact.js';
import { initViewContact } from '../utils/contacts/view-contact.js';
import { initDataTable } from '../components/data-table.js';

export function init() {
    initContactsModal();
    initDeleteContact();
    initViewContact();

    initDataTable({
        tbodyId: 'contacts-tbody',
        countId: 'contacts-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/contacts`,
        resourceLabel: 'contact',
        colspan: 6,
        defaultSort: 'contact',
        defaultDir: 'asc',
    });
}
