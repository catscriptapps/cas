// /resources/js/modals/sections-modal.js

import { Modal } from '../factories/modal-factory.js';
import { sectionForm } from '../forms/section-form.js';
import { enableIconPicker } from '../components/icon-picker.js';
import { handleSectionFormSubmission } from '../utils/questions/section-form-submit.js';

let sectionModal = null;

function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    handleSectionFormSubmission(form, mode, modalInstance);
    enableIconPicker(formId);
}

export function openAddSectionModal() {
    if (sectionModal) sectionModal.destroy();

    sectionModal = new Modal({
        id: 'add-section-modal',
        title: 'New Section',
        content: sectionForm({
            mode: 'add',
            formId: 'section-add-form',
            buttonLabel: 'Create Section',
        }),
        size: 'md',
        showFooter: false,
    });

    sectionModal.open();
    initFormFeatures('section-add-form', 'add', sectionModal);
}

export function openEditSectionModal({ encodedId, name, icon }) {
    if (sectionModal) sectionModal.destroy();

    sectionModal = new Modal({
        id: 'edit-section-modal',
        title: 'Edit Section',
        content: sectionForm({
            mode: 'edit',
            formId: 'section-edit-form',
            name: name || '',
            icon: icon || 'general',
            buttonLabel: 'Save Changes',
            encodedId,
        }),
        size: 'md',
        showFooter: false,
    });

    sectionModal.open();
    initFormFeatures('section-edit-form', 'edit', sectionModal);
}

let listenersAttached = false;
export function initSectionsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        if (e.target.closest('#add-section-btn')) {
            e.preventDefault();
            openAddSectionModal();
        }
    });

    listenersAttached = true;
}
