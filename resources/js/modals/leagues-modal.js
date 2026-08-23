// /resources/js/modals/leagues-modal.js

import { Modal } from '../factories/modal-factory.js';
import { leagueForm } from '../forms/league-form.js';
import { handleLeagueFormSubmission } from '../utils/leagues/form-submit.js';
import { fetchAllSports } from '../api/divisions-api.js';

let leagueModal = null;

async function openModal(title, formData) {
    const sports = await fetchAllSports();

    if (leagueModal) leagueModal.destroy();
    leagueModal = new Modal({ id: 'league-modal', title, content: leagueForm({ ...formData, sports }), size: 'sm', showFooter: false });
    leagueModal.open();

    const form = document.getElementById('league-form');
    if (form) handleLeagueFormSubmission(form, leagueModal);
}

export function openAddLeagueModal() {
    openModal('Add League', {});
}

export function openEditLeagueModal(trigger) {
    const btn = trigger.closest('.edit-league-btn') || trigger;
    const data = btn.dataset;

    openModal('Edit League', {
        league: data.league || '',
        sportId: data.sportId || '',
        isBall: data.isBall === '1',
        isActive: data.isActive === '1',
        encodedId: data.encodedId,
    });
}

let listenersAttached = false;
export function initLeaguesModal() {
    if (listenersAttached) return;

    document.getElementById('add-league-btn')?.addEventListener('click', openAddLeagueModal);

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-league-btn');
        if (editBtn) {
            e.preventDefault();
            openEditLeagueModal(editBtn);
        }
    });

    listenersAttached = true;
}
