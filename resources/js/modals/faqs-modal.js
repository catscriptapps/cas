// /resources/js/modals/faqs-modal.js

import { Modal } from '../factories/modal-factory.js';
import { faqForm } from '../forms/faq-form.js';
import { handleFaqFormSubmission } from '../utils/faqs/form-submit.js';

let faqModal = null;

function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;
    handleFaqFormSubmission(form, mode, modalInstance);
}

function openAddFaqModal() {
    if (faqModal) faqModal.destroy();
    faqModal = new Modal({
        id: 'add-faq-modal',
        title: 'New FAQ Entry',
        content: faqForm({ mode: 'add', formId: 'faq-add-form', buttonLabel: 'Create FAQ' }),
        size: 'md',
        showFooter: false,
    });
    faqModal.open();
    initFormFeatures('faq-add-form', 'add', faqModal);
}

function openEditFaqModal(trigger) {
    const data = trigger.dataset;
    if (faqModal) faqModal.destroy();
    faqModal = new Modal({
        id: 'edit-faq-modal',
        title: 'Edit FAQ Entry',
        content: faqForm({
            mode: 'edit',
            formId: 'faq-edit-form',
            question: data.question,
            answer: data.answer,
            displayOrder: data.displayOrder,
            statusId: data.statusId,
            encodedId: data.encodedId,
            buttonLabel: 'Save Changes'
        }),
        size: 'md',
        showFooter: false,
    });
    faqModal.open();
    initFormFeatures('faq-edit-form', 'edit', faqModal);
}

let listenersAttached = false;

export function initFaqsModal() {
    if (listenersAttached) return;

    // Use 'false' for the third argument (Bubbling) instead of 'true' (Capture)
    // to play nicely with other global listeners.
    document.addEventListener('click', (e) => {
        
        // 1. Handle Add Button
        const addBtn = e.target.closest('#add-faq-btn');
        if (addBtn) {
            // REMOVED preventDefault and stopPropagation
            // This allows table-search.js to see the click and reset the results
            openAddFaqModal();
            return;
        }

        // 2. Handle Edit Button
        const editBtn = e.target.closest('.edit-faq-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation(); // Keep this one so the accordion doesn't toggle
            openEditFaqModal(editBtn);
        }
    }); 

    listenersAttached = true;
}