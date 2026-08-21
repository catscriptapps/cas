// /resources/js/pages/companies-page.js

import { initCompaniesModal } from '../modals/companies-modal.js';
import { updateCount } from '../components/table-pagination-count.js';
import { initDeleteCompany } from '../utils/companies/delete-company.js';
import { enableTableSearch } from '../components/table-search.js';
import { initViewCompany } from '../utils/companies/view-company.js';
import { initCompanyInfiniteScroll } from '../utils/companies/infinite-scroll-companies.js';
import { initCompanyAdminsPanel } from '../utils/companies/company-admins.js';
import { initCompanyLogoPanel } from '../utils/companies/company-logo.js';

/**
 * Initialize the Companies page JS.
 */
export function init() {
    // 1. Initialize the Create/Edit modal logic
    initCompaniesModal();

    // 2. Enable the AJAX search
    enableTableSearch({
        searchInputId: 'companies-search',
        tbodyId: 'companies-tbody',
        countId: 'companies-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/companies`,
        resourceLabel: 'company',
        addButtonId: 'add-company-btn'
    });

    // 3. Initialize the delete functionality
    initDeleteCompany();

    // 4. Initial count check
    updateCount('company', '#companies-tbody', '#companies-count');

    // 5. Initialize the detailed view modal
    initViewCompany();

    // 6. Initialize Infinite Scroll
    initCompanyInfiniteScroll();

    // 7. Company Admins panel (inside the View Company modal)
    initCompanyAdminsPanel();

    // 8. Company Logo upload/preview/delete (inside the View Company modal)
    initCompanyLogoPanel();
}
