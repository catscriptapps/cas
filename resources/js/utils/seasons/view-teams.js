// /resources/js/utils/seasons/view-teams.js

import { handleTeamFormSubmission } from './team-form-submit.js';
import { handleDeleteTeam } from './team-delete.js';
import { teamItemTemplate } from './team-templates.js';
import { PlayerViewManager } from './players-view.js';
import { loadRosterData, initAddPlayerForm, handleRemovePlayer, handleToggleGoalie } from './player-actions.js';
import { fetchTeamRoster } from '../../api/players-api.js';

/**
 * Manages the View Teams modal and its Team/Player sub-views.
 */
export function initViewTeams() {
    const addPlayerForm = document.getElementById('add-player-form');
    if (addPlayerForm) {
        initAddPlayerForm(addPlayerForm);
    }

    const renderTeamsList = (teams) => {
        const listContainer = document.getElementById('teams-list-container');
        const countBadgeEl = document.getElementById('view-season-team-count');
        if (!listContainer) return;

        if (countBadgeEl) {
            countBadgeEl.textContent = `${teams.length} ${teams.length === 1 ? 'Team' : 'Teams'} Registered`;
        }

        const sortedTeams = [...teams].sort((a, b) =>
            (a.team_name || '').localeCompare(b.team_name || '')
        );

        listContainer.innerHTML = sortedTeams.length > 0
            ? sortedTeams.map(team => teamItemTemplate(team)).join('')
            : `<div class="text-center py-8 text-gray-500 italic text-sm">No teams registered.</div>`;
    };

    if (window._viewTeamsInitialized) return;
    window._viewTeamsInitialized = true;

    const togglePlayerView = (show = false, teamId = null, teamName = null) => {
        const listView = document.getElementById('modal-list-view');
        const formView = document.getElementById('add-team-form');
        const modal = document.getElementById('view-teams-modal');
        const modalFooter = modal?.querySelector('.px-6.py-4.border-t');

        if (show) {
            listView?.classList.add('hidden');
            formView?.classList.add('hidden');
            modalFooter?.classList.add('hidden');
            PlayerViewManager.show(teamId, teamName);
        } else {
            PlayerViewManager.hide();
            listView?.classList.remove('hidden');
            modalFooter?.classList.remove('hidden');
        }
    };

    const toggleView = (showForm = false, teamData = null) => {
        const listView = document.getElementById('modal-list-view');
        const formView = document.getElementById('add-team-form');
        const playerView = document.getElementById('player-management-view');
        const modal = document.getElementById('view-teams-modal');
        const modalFooter = modal?.querySelector('.px-6.py-4.border-t');

        playerView?.classList.add('hidden');

        if (showForm) {
            listView?.classList.add('hidden');
            modalFooter?.classList.add('hidden');
            formView?.classList.remove('hidden');

            let idInput = formView.querySelector('[name="team_id"]');
            if (!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'team_id';
                formView.appendChild(idInput);
            }

            if (teamData) {
                const setVal = (name, val) => {
                    const el = formView.querySelector(`[name="${name}"]`);
                    if (el) el.value = val || '';
                };

                setVal('team_name', teamData.team_name);
                setVal('team_number', teamData.team_number);
                setVal('team_group', teamData.team_group);
                setVal('team_rep_id', teamData.team_rep_id);
                idInput.value = teamData.team_id;

                const titleEl = formView.querySelector('h3');
                if (titleEl) titleEl.textContent = 'Edit Team';
            } else {
                formView.reset();
                idInput.value = '';
                const titleEl = formView.querySelector('h3');
                if (titleEl) titleEl.textContent = 'Add New Team';
            }

            handleTeamFormSubmission(formView);
        } else {
            listView?.classList.remove('hidden');
            modalFooter?.classList.remove('hidden');
            formView?.classList.add('hidden');
            formView?.reset();

            const apiMsg = formView?.querySelector('.api-message');
            if (apiMsg) apiMsg.innerHTML = '';
        }
    };

    window.addEventListener('teamsUpdated', (e) => {
        const currentSeasonId = document.getElementById('form-season-id')?.value;
        if (e.detail.teams && (!e.detail.seasonId || e.detail.seasonId == currentSeasonId)) {
            renderTeamsList(e.detail.teams);
        }
    });

    document.addEventListener('teamRosterUpdated', async (e) => {
        const { teamId } = e.detail;
        const badge = document.getElementById(`badge-count-${teamId}`);
        const allTriggers = document.querySelectorAll('.view-teams-trigger');

        if (badge) {
            try {
                const players = await fetchTeamRoster(teamId);
                const newCount = players.length;
                badge.textContent = newCount;

                allTriggers.forEach(btn => {
                    if (!btn.dataset.teams) return;
                    let teams = JSON.parse(btn.dataset.teams);
                    let isTargetSeason = false;
                    const updatedTeams = teams.map(t => {
                        if (t.team_id == teamId) {
                            isTargetSeason = true;
                            return { ...t, player_count: newCount };
                        }
                        return t;
                    });
                    if (isTargetSeason) btn.dataset.teams = JSON.stringify(updatedTeams);
                });

                badge.classList.remove('scale-100');
                badge.classList.add('scale-125', 'bg-emerald-500');
                setTimeout(() => {
                    badge.classList.remove('scale-125', 'bg-emerald-500');
                    badge.classList.add('scale-100');
                }, 400);
            } catch (err) { console.error(err); }
        }
    });

    document.addEventListener('click', (e) => {
        const modal = document.getElementById('view-teams-modal');

        const editTrigger = e.target.closest('.team-edit-trigger');
        if (editTrigger) {
            e.preventDefault();
            const teamData = editTrigger.dataset.teamEncoded
                ? JSON.parse(decodeURIComponent(editTrigger.dataset.teamEncoded))
                : JSON.parse(editTrigger.dataset.team || '{}');

            toggleView(true, teamData);
            return;
        }

        const playerTrigger = e.target.closest('.team-players-trigger');
        if (playerTrigger) {
            e.preventDefault();
            const { teamId, teamName } = playerTrigger.dataset;
            togglePlayerView(true, teamId, teamName);
            loadRosterData(teamId);
            return;
        }

        const playerDeleteInit = e.target.closest('.player-delete-init');
        if (playerDeleteInit) {
            e.preventDefault();
            playerDeleteInit.classList.add('hidden');
            playerDeleteInit.parentElement.querySelector('.player-delete-confirm').classList.remove('hidden');
            return;
        }

        const goalieBtn = e.target.closest('.toggle-goalie-btn');
        if (goalieBtn) {
            e.preventDefault();
            handleToggleGoalie(goalieBtn);
            return;
        }

        const playerDeleteCancel = e.target.closest('.player-delete-cancel');
        if (playerDeleteCancel) {
            e.preventDefault();
            const parent = playerDeleteCancel.closest('.relative');
            parent.querySelector('.player-delete-confirm').classList.add('hidden');
            parent.querySelector('.player-delete-init').classList.remove('hidden');
            return;
        }

        const playerDeleteConfirm = e.target.closest('.player-delete-confirm-btn');
        if (playerDeleteConfirm) {
            e.preventDefault();
            playerDeleteConfirm.disabled = true;
            handleRemovePlayer(playerDeleteConfirm);
            return;
        }

        if (e.target.closest('#back-to-teams')) {
            e.preventDefault();
            togglePlayerView(false);
            return;
        }

        const deleteInit = e.target.closest('.team-delete-init');
        if (deleteInit) {
            e.preventDefault();
            deleteInit.classList.add('hidden');
            deleteInit.parentElement.querySelector('.team-delete-confirm').classList.remove('hidden');
            return;
        }

        const deleteCancel = e.target.closest('.team-delete-cancel');
        if (deleteCancel) {
            e.preventDefault();
            const parent = deleteCancel.closest('.relative.flex.items-center');
            parent.querySelector('.team-delete-confirm').classList.add('hidden');
            parent.querySelector('.team-delete-init').classList.remove('hidden');
            return;
        }

        const deleteConfirm = e.target.closest('.team-delete-confirm-btn');
        if (deleteConfirm) {
            e.preventDefault();
            e.stopImmediatePropagation();
            deleteConfirm.disabled = true;
            const currentSeasonId = document.getElementById('form-season-id').value;
            handleDeleteTeam(deleteConfirm.dataset.teamId, currentSeasonId);
            return;
        }

        if (e.target.closest('#show-add-team-form')) {
            toggleView(true);
            return;
        }
        if (e.target.closest('#hide-add-team-form')) {
            toggleView(false);
            return;
        }

        const trigger = e.target.closest('.view-teams-trigger');
        if (trigger) {
            if (!modal) return;
            const data = trigger.dataset;
            toggleView(false);

            document.getElementById('view-season-division-name').textContent = data.divisionName || '';
            document.getElementById('view-season-division-name').dataset.divisionId = data.divisionId || '';
            document.getElementById('view-season-year-display').textContent = `Season Year: ${data.seasonYear || ''}`;
            document.getElementById('form-season-id').value = data.seasonId || '';

            renderTeamsList(data.teams ? JSON.parse(data.teams) : []);
            modal.classList.remove('hidden');
        }

        if (e.target.closest('.close-teams-modal') || e.target.id === 'close-teams-modal-overlay') {
            modal?.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.getElementById('view-teams-modal')?.classList.add('hidden');
        }
    });
}
