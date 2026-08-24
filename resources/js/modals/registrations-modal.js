// /resources/js/modals/registrations-modal.js

import { Modal } from '../factories/modal-factory.js';
import { registrationForm } from '../forms/registration-form.js';
import { handleRegistrationFormSubmission } from '../utils/registrations/form-submit.js';

let registrationModal = null;

export async function openEditRegistrationModal(trigger) {
    const btn = trigger.closest('.edit-registration-btn') || trigger;
    if (!btn?.dataset) return;

    const data = btn.dataset;

    if (registrationModal) registrationModal.destroy();

    registrationModal = new Modal({
        id: 'edit-registration-modal',
        title: 'Edit Registration',
        content: registrationForm({
            fullName: data.fullName || '',
            email: data.email || '',
            phone: data.phone || '',
            city: data.city || '',
            divisionName: data.divisionName || '',
            position: data.position || '',
            teamName: data.teamName || '',
            isActive: data.isActive === '1',
            hasPaid: data.hasPaid === '1',
            amountPaid: data.amountPaid || 0,
            encodedId: data.encodedId,
        }),
        size: 'md',
        showFooter: false,
    });

    registrationModal.open();

    const form = document.getElementById('registrations-edit-form');
    if (form) handleRegistrationFormSubmission(form, registrationModal);
}

let listenersAttached = false;
export function initRegistrationsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-registration-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditRegistrationModal(editBtn);
        }
    });

    listenersAttached = true;
}
