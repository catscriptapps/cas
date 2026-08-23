// /resources/js/modals/sports-modal.js

import { Modal } from '../factories/modal-factory.js';
import { sportForm } from '../forms/sport-form.js';
import { handleSportFormSubmission } from '../utils/sports/form-submit.js';

let sportModal = null;

function openModal(title, formHtml) {
    if (sportModal) sportModal.destroy();
    sportModal = new Modal({ id: 'sport-modal', title, content: formHtml, size: 'sm', showFooter: false });
    sportModal.open();

    const form = document.getElementById('sport-form');
    if (form) handleSportFormSubmission(form, sportModal);
}

export function openAddSportModal() {
    openModal('Add Sport', sportForm({}));
}

export function openEditSportModal(trigger) {
    const btn = trigger.closest('.edit-sport-btn') || trigger;
    const data = btn.dataset;

    openModal('Edit Sport', sportForm({
        sportName: data.sportName || '',
        isActive: data.isActive === '1',
        encodedId: data.encodedId,
    }));
}

let listenersAttached = false;
export function initSportsModal() {
    if (listenersAttached) return;

    document.getElementById('add-sport-btn')?.addEventListener('click', openAddSportModal);

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-sport-btn');
        if (editBtn) {
            e.preventDefault();
            openEditSportModal(editBtn);
        }
    });

    listenersAttached = true;
}
