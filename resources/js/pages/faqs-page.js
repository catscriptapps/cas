// /resources/js/pages/faqs-page.js

import { initFaqsModal } from '../modals/faqs-modal.js';
import { updateCount } from '../components/table-pagination-count.js';
import { initDeleteFaq } from '../utils/faqs/delete-faq.js';
import { enableTableSearch } from '../components/table-search.js';
import { initFaqAccordion } from '../utils/faqs/faq-accordion.js';

/**
 * Initialize the FAQs page JS.
 */
export function init() {
    // 1. Initialize the Add/Edit Modal & Form Logic
    initFaqsModal();
    
    // 2. Enable the pro AJAX search for FAQs
    // Note: We target the faqs-container instead of a tbody since it's an accordion list
    enableTableSearch({
        searchInputId: 'faqs-search',
        tbodyId: 'faqs-container', 
        countId: 'faqs-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/faqs`,
        resourceLabel: 'faq',
        addButtonId: 'add-faq-btn'
    });

    // 3. Initialize the delete listener for FAQ cards
    initDeleteFaq();

    // 4. Initialize the smooth accordion toggle logic
    initFaqAccordion();

    // 5. Initial count update
    // This ensures the "Showing X faqs" footer is accurate on page load
    updateCount('faq', '#faqs-container', '#faqs-count');
}