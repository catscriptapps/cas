// /resources/js/utils/faqs/delete-faq.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Attaches delete functionality to the FAQs container via delegation.
 */
export function initDeleteFaq(containerSelector = '#faqs-container') {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    // Initialize the factory for the FAQs endpoint
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/faqs`, 'FAQ');

    // Delegate click to delete buttons
    container.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-faq-btn');
        if (!btn) return;

        e.stopPropagation(); // Stop the accordion from toggling when clicking delete

        const card = btn.closest('.faq-item');
        const encodedId = card?.dataset.encodedId;

        if (!encodedId || !card) {
            console.error('Delete failed: Missing encoded ID or card element.');
            return;
        }

        // Trigger the factory's confirmation modal
        deleteHandler.showConfirmation(encodedId, card, (success) => {
            if (!success) return;

            // 1. Show high-contrast toast
            showToast('FAQ successfully deleted', 'success');

            // 2. Update the count in the footer
            updateCount('faq', containerSelector, '#faqs-count');

            // 3. Handle empty state if no cards are left
            const remainingCards = container.querySelectorAll('.faq-item').length;
            if (remainingCards === 0) {
                container.innerHTML = `
                    <div class="p-20 rounded-[3rem] bg-gray-50 dark:bg-gray-900/30 border-2 border-dashed border-gray-200 dark:border-gray-800 text-center animate-in fade-in zoom-in duration-500">
                        <div class="flex flex-col items-center">
                            <svg class="h-16 w-16 text-gray-300 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8.227 9.164a3.001 3.001 0 115.546 1.672c-.57 1.11-1.77 1.528-2.81 2.003a.75.75 0 00-.416.68v.75m.75 3h.008" />
                            </svg>
                            <p class="text-xl font-black text-gray-400 dark:text-gray-600">No questions found.</p>
                            <p class="text-sm text-gray-500 mt-2 font-medium">The knowledge base is currently empty.</p>
                        </div>
                    </div>
                `;
            }
        });
    });
}