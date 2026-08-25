// /resources/js/modals/seasons-modal.js

import { Modal } from '../factories/modal-factory.js';
import { seasonForm } from '../forms/season-form.js';
import { handleSeasonFormSubmission } from '../utils/seasons/form-submit.js';
import { fetchAllDivisionsGrouped } from '../api/divisions-api.js';

let seasonsModal = null;

async function openAddSeasonModal() {
    const divisions = await fetchAllDivisionsGrouped();

    if (seasonsModal) seasonsModal.destroy();

    seasonsModal = new Modal({
        id: 'add-season-modal',
        title: 'Create New Season Entry',
        content: seasonForm({
            mode: 'add',
            formId: 'season-add-form',
            buttonLabel: 'Create Season',
            divisionList: divisions
        }),
        size: 'md',
        showFooter: false,
    });

    seasonsModal.open();

    const form = document.getElementById('season-add-form');
    if (form) {
        handleSeasonFormSubmission(form, 'add', seasonsModal);
    }
}

let listenersAttached = false;

export function initSeasonsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#add-season-btn');
        if (addBtn) {
            openAddSeasonModal();
        }
    });

    listenersAttached = true;
}
