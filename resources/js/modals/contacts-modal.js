// /resources/js/modals/contacts-modal.js

import { Modal } from '../factories/modal-factory.js';
import { contactForm } from '../forms/contact-form.js';
import { handleContactFormSubmission } from '../utils/contacts/form-submit.js';
import { attachPhoneFormatter } from '../utils/phone-formatter.js';

let contactModal = null;

function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    handleContactFormSubmission(form, mode, modalInstance);
    attachPhoneFormatter();
}

async function openAddContactModal() {
    if (contactModal) contactModal.destroy();

    contactModal = new Modal({
        id: 'add-contact-modal',
        title: 'New Contact',
        content: await contactForm({
            mode: 'add',
            formId: 'contacts-add-form',
            buttonLabel: 'Create Contact'
        }),
        size: 'lg',
        showFooter: false,
    });

    contactModal.open();
    initFormFeatures('contacts-add-form', 'add', contactModal);
}

async function openEditContactModal(trigger) {
    if (!trigger?.dataset) return;

    const data = trigger.dataset;

    if (contactModal) contactModal.destroy();

    contactModal = new Modal({
        id: 'edit-contact-modal',
        title: 'Edit Contact',
        content: await contactForm({
            mode: 'edit',
            formId: 'contacts-edit-form',
            fullName: data.fullName,
            organization: data.organization,
            email: data.email,
            phone: data.phone,
            leagues: data.leagues,
            roleId: parseInt(data.roleId, 10),
            isEmergency: data.isEmergency === "1",
            buttonLabel: 'Save Changes',
            encodedId: data.encodedId
        }),
        size: 'lg',
        showFooter: false,
    });

    contactModal.open();
    initFormFeatures('contacts-edit-form', 'edit', contactModal);
}

let listenersAttached = false;

export function initContactsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#add-contact-btn');
        if (addBtn) {
            e.preventDefault();
            openAddContactModal();
            return;
        }

        const editBtn = e.target.closest('.edit-contact-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditContactModal(editBtn);
            return;
        }
    });

    listenersAttached = true;
}
