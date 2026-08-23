// /resources/js/modals/divisions-modal.js

import { Modal } from '../factories/modal-factory.js';
import { divisionForm } from '../forms/division-form.js';
import { handleDivisionFormSubmission } from '../utils/divisions/form-submit.js';
import { fetchAllSports, fetchAllLeagues } from '../api/divisions-api.js';

let divisionModal = null;

async function loadSportsWithLeagues() {
    const sports = await fetchAllSports();
    const withLeagues = await Promise.all(sports.map(async (sport) => ({
        name: sport.name,
        leagues: await fetchAllLeagues(sport.id),
    })));
    return withLeagues;
}

async function openModal(title, formData) {
    const sportsWithLeagues = await loadSportsWithLeagues();

    if (divisionModal) divisionModal.destroy();
    divisionModal = new Modal({ id: 'division-modal', title, content: divisionForm({ ...formData, sportsWithLeagues }), size: 'sm', showFooter: false });
    divisionModal.open();

    const form = document.getElementById('division-form');
    if (form) handleDivisionFormSubmission(form, divisionModal);
}

export function openAddDivisionModal() {
    openModal('Add Division', {});
}

export function openEditDivisionModal(trigger) {
    const btn = trigger.closest('.edit-division-btn') || trigger;
    const data = btn.dataset;

    openModal('Edit Division', {
        divisionName: data.division || '',
        leagueId: data.leagueId || '',
        price: data.price || 0,
        isActive: data.isActive === '1',
        encodedId: data.encodedId,
    });
}

let listenersAttached = false;
export function initDivisionsModal() {
    if (listenersAttached) return;

    document.getElementById('add-division-btn')?.addEventListener('click', openAddDivisionModal);

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-division-btn');
        if (editBtn) {
            e.preventDefault();
            openEditDivisionModal(editBtn);
        }
    });

    listenersAttached = true;
}
