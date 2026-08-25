// /resources/js/utils/seasons/team-delete.js

import { showToast } from '../../ui/toast.js';

export async function handleDeleteTeam(teamId, seasonId) {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/teams-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: teamId })
        });

        const result = await response.json();

        if (result.success) {
            const currentSeasonId = seasonId || document.getElementById('form-season-id')?.value;
            const triggerBtns = document.querySelectorAll(`.view-teams-trigger[data-season-id="${currentSeasonId}"]`);

            let updatedTeamsList = [];

            triggerBtns.forEach(btn => {
                try {
                    const teams = JSON.parse(btn.dataset.teams || '[]');
                    updatedTeamsList = teams.filter(t => (t.team_id || t.id) != teamId);

                    btn.dataset.teams = JSON.stringify(updatedTeamsList);

                    const countLabel = btn.querySelector('.team-count-label') || btn.lastChild;
                    if (countLabel) {
                        const count = updatedTeamsList.length;
                        countLabel.textContent = ` ${count} ${count === 1 ? 'Team' : 'Teams'}`;
                    }
                } catch (parseErr) {
                    console.warn('Failed to update a trigger button dataset', parseErr);
                }
            });

            window.dispatchEvent(new CustomEvent('teamsUpdated', {
                detail: {
                    seasonId: currentSeasonId,
                    teams: updatedTeamsList
                }
            }));

            showToast('Team removed successfully', 'success');
        } else {
            showToast(result.messages?.[0] || 'Failed to delete team', 'error');
        }
    } catch (err) {
        console.error('Delete Error:', err);
        showToast('UI update error, but team may have been deleted', 'error');
    }
}
