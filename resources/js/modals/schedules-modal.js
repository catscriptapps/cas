// /resources/js/modals/schedules-modal.js

import { Modal } from '../factories/modal-factory.js';
import { scheduleForm } from '../forms/schedule-form.js';
import { handleScheduleFormSubmission } from '../utils/schedules/form-submit.js';
import { fetchLocations } from '../api/locations-api.js';
import { fetchTeams } from '../api/teams-api.js';

let scheduleModal = null;

function initFormFeatures(formId, mode, modalInstance, providedSeasonId = null) {
    const form = document.getElementById(formId);
    if (!form) return;

    const seasonIdEnc = providedSeasonId || document.querySelector('[data-season-encoded-id]')?.dataset.seasonEncodedId;

    if (!seasonIdEnc) {
        console.error('CRITICAL: Season ID not found. Submission will fail.');
    }

    handleScheduleFormSubmission(form, mode, modalInstance, seasonIdEnc);
}

async function openAddScheduleModal() {
    const locations = await fetchLocations();

    if (scheduleModal) scheduleModal.destroy();

    scheduleModal = new Modal({
        id: 'add-schedule-modal',
        title: 'Add New Game',
        content: scheduleForm({
            mode: 'add',
            formId: 'schedules-add-form',
            buttonLabel: 'Create Schedule',
            locations: locations
        }),
        size: 'md',
        showFooter: false,
    });

    scheduleModal.open();
    initFormFeatures('schedules-add-form', 'add', scheduleModal);
}

async function openEditScheduleModal(trigger) {
    if (!trigger?.dataset) return;

    const data = trigger.dataset;
    const specificSeasonId = trigger.getAttribute('data-season-id') || trigger.dataset.seasonId;

    if (!specificSeasonId) {
        console.warn('No Season ID found on the edit button!');
    }

    const [locations, teams] = await Promise.all([
        fetchLocations(),
        specificSeasonId ? fetchTeams(specificSeasonId) : Promise.resolve([])
    ]);

    if (scheduleModal) scheduleModal.destroy();

    scheduleModal = new Modal({
        id: 'edit-schedule-modal',
        title: 'Edit Game',
        content: scheduleForm({
            mode: 'edit',
            formId: 'schedules-edit-form',
            buttonLabel: 'Save Changes',
            locations: locations,
            teams: teams,
            gameId: data.gameId,
            gameDate: data.gameDate,
            gameTime: data.gameTime,
            locationId: data.locationId,
            homeTeamId: data.homeTeamId,
            awayTeamId: data.awayTeamId,
            ref1: data.ref1,
            ref2: data.ref2,
            timekeep: data.timekeep,
            isPlayoff: data.isPlayoff
        }),
        size: 'md',
        showFooter: false,
    });

    scheduleModal.open();
    initFormFeatures('schedules-edit-form', 'edit', scheduleModal, specificSeasonId);
}

let listenersAttached = false;

export function initSchedulesModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#add-schedule-btn');
        if (addBtn) {
            e.preventDefault();
            openAddScheduleModal();
            return;
        }

        const editBtn = e.target.closest('.edit-schedule-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditScheduleModal(editBtn);
            return;
        }
    });

    listenersAttached = true;
}
