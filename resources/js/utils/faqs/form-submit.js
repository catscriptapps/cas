// /resources/js/utils/faqs/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { updateCount } from '../../components/table-pagination-count.js';
import { initFaqAccordion } from './faq-accordion.js';

/**
 * Maps form data to an API payload for FAQs
 */
function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    return {
        // Grab encoded_id from the hidden input we added to the form template
        encoded_id: data.encoded_id || null,
        question: data.question?.trim(),
        answer: data.answer?.trim(),
        display_order: data.display_order || 0,
        status_id: data.status_id || 1,
    };
}

/**
 * Handles Add/Edit FAQ form submission
 */
export function handleFaqFormSubmission(
    form, 
    mode, 
    modalInstance, 
    containerSelector = '#faqs-container'
) {
    if (form._faqFormListenerAttached) return;
    form._faqFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    let apiMsg = form.querySelector('.api-message');

    if (!apiMsg) {
        apiMsg = document.createElement('div');
        apiMsg.className = 'api-message mt-4 transition-all duration-300';
        form.insertBefore(apiMsg, submitBtn.parentElement); // Place message above the button
    }

    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validator.validateForEmptyFields(e)) return;

        // UI State: Loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner; 
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);
            
            // Override method for PHP if necessary
            if (mode === 'edit') payload._method = 'POST'; 

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/faqs`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                // 1. --- CONTAINER UPDATE LOGIC ---
                const container = document.querySelector(containerSelector);
                
                if (container) {
                    /**
                     * If the server returns fullHtml, we replace the whole list.
                     * This handles the "Displacement Logic" where multiple rows 
                     * may have shifted their sort order and Q numbers.
                     */
                    if (result.fullHtml) {
                        container.innerHTML = result.fullHtml;
                    } 
                    // Fallback for single row updates if fullHtml isn't provided
                    else if (mode === 'edit' && result.rowHtml) {
                        const existingCard = document.getElementById(`faq-row-${result.data?.id}`) || 
                                           document.querySelector(`div[data-encoded-id="${payload.encoded_id}"]`);
                        
                        if (existingCard) {
                            existingCard.outerHTML = result.rowHtml;
                        }
                    } else if (result.rowHtml) {
                        container.insertAdjacentHTML('afterbegin', result.rowHtml);
                    }

                    // Re-init features for the new content
                    initFaqAccordion();
                    updateCount('faq', containerSelector, '#faqs-count');
                }

                // 2. --- SUCCESS FEEDBACK ---
                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">
                        ${result.messages?.[0] || 'FAQ saved successfully.'}
                    </div>
                `;

                // 3. --- CLEANUP & CLOSE ---
                submitBtn.style.visibility = 'hidden'; 
                
                setTimeout(() => {
                    if (modalInstance && typeof modalInstance.close === 'function') {
                        modalInstance.close();
                    }
                }, 1000);

            } else {
                apiMsg.innerHTML = (result.messages || ['Check your input']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">
                        ${msg}
                    </div>
                `).join('');
            }

        } catch (err) {
            console.error('Submission Error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">Unexpected error.</div>`;
        } finally {
            if (submitBtn.style.visibility !== 'hidden') {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}