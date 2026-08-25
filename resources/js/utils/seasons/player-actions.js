// /resources/js/utils/seasons/player-actions.js

import {
    fetchTeamRoster,
    fetchAvailableRegistrants,
    addPlayerToTeam,
    removePlayerFromTeam,
    updatePlayerGoalieStatus
} from '../../api/players-api.js';
import { playerRowTemplate } from './player-templates.js';
import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { showToast } from '../../ui/toast.js';

export async function loadRosterData(teamId) {
    const listContainer = document.getElementById('players-list-container');
    const selectDropdown = document.getElementById('registration-search-select');
    const countBadge = document.getElementById('view-team-player-count');

    listContainer.innerHTML = `
        <div class="flex flex-col items-center justify-center py-12">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-500 mb-2"></div>
            <div class="text-gray-400 italic text-[10px] uppercase tracking-widest font-bold">Refreshing Roster</div>
        </div>`;

    selectDropdown.innerHTML = '<option value="">Scanning registrations...</option>';

    const [roster, available] = await Promise.all([
        fetchTeamRoster(teamId),
        fetchAvailableRegistrants(teamId)
    ]);

    if (countBadge) {
        countBadge.textContent = `${roster.length} ${roster.length === 1 ? 'Player' : 'Players'} Assigned`;
    }

    listContainer.innerHTML = roster.length > 0
        ? roster.map(player => playerRowTemplate(player)).join('')
        : '<div class="p-8 text-center text-gray-400 italic text-sm border-2 border-dashed border-gray-100 rounded-xl">No players assigned yet.</div>';

    let selectHtml = '<option value="">-- Select a Registered Player --</option>';
    if (available.length > 0) {
        available.forEach(reg => {
            selectHtml += `<option value="${reg.entry_id}">${reg.full_name} (${reg.position || 'N/A'})</option>`;
        });
    } else {
        selectHtml = '<option value="">No eligible players found.</option>';
    }
    selectDropdown.innerHTML = selectHtml;
}

export function initAddPlayerForm(form) {
    if (!form || form._playerFormListenerAttached) return;
    form._playerFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('#add-player-to-roster-btn');
    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;

        const formData = new FormData(form);
        const teamId = document.getElementById('player-form-team-id')?.value;
        const seasonId = document.getElementById('form-season-id')?.value;
        const userId = formData.get('user_id');

        const payload = {
            team_id: parseInt(teamId),
            user_id: parseInt(userId),
            season_id: parseInt(seasonId),
            is_goalie: form.querySelector('#player-is-goalie').checked ? 1 : 0
        };

        try {
            const result = await addPlayerToTeam(payload);

            if (result.success) {
                showToast(result.messages?.[0] || 'Player added successfully', 'success');

                form.reset();
                await loadRosterData(payload.team_id);

                dispatchRosterUpdate(payload.team_id);
            } else {
                const errorText = result.messages?.[0] || 'Failed to add player';
                showToast(errorText, 'error');
            }
        } catch (err) {
            showToast('Connection Error', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;
        }
    });
}

export async function handleRemovePlayer(btn) {
    const playerId = btn.dataset.playerId;
    const teamId = document.getElementById('player-form-team-id').value;

    btn.disabled = true;
    try {
        const result = await removePlayerFromTeam(playerId);

        if (result.success) {
            showToast('Player removed successfully', 'success');
            await loadRosterData(teamId);

            dispatchRosterUpdate(teamId);
        } else {
            btn.disabled = false;
            showToast(result.messages?.[0] || 'Failed to remove player', 'error');
        }
    } catch (err) {
        btn.disabled = false;
        showToast('Connection error', 'error');
    }
}

export async function handleToggleGoalie(btn) {
    const playerId = btn.dataset.playerId;
    const currentStatus = btn.dataset.isGoalie === 'true';
    const teamId = document.getElementById('player-form-team-id').value;

    const newStatus = currentStatus ? 0 : 1;

    btn.disabled = true;
    btn.classList.add('animate-pulse');

    try {
        const result = await updatePlayerGoalieStatus(parseInt(playerId), newStatus);

        if (result.success) {
            showToast(newStatus ? 'Player set as Goalie' : 'Goalie status removed', 'success');
            await loadRosterData(teamId);
        } else {
            showToast(result.messages?.[0] || 'Update failed', 'error');
            btn.disabled = false;
            btn.classList.remove('animate-pulse');
        }
    } catch (err) {
        showToast('Network error', 'error');
        btn.disabled = false;
        btn.classList.remove('animate-pulse');
    }
}

function dispatchRosterUpdate(teamId) {
    document.dispatchEvent(new CustomEvent('teamRosterUpdated', {
        detail: { teamId: parseInt(teamId) }
    }));
}
