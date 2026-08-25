// /resources/js/utils/seasons/team-form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';

function getPayload(form) {
    const formData = new FormData(form);
    return Object.fromEntries(formData.entries());
}

export function handleTeamFormSubmission(form) {
    if (form._teamFormListenerAttached) return;
    form._teamFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const cancelBtn = document.getElementById('hide-add-team-form');
    let apiMsg = form.querySelector('.api-message');

    if (!apiMsg) {
        apiMsg = document.createElement('div');
        apiMsg.className = 'api-message mt-4 transition-all duration-300';
        form.appendChild(apiMsg);
    }

    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        if (cancelBtn) cancelBtn.classList.add('hidden');
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);
            const isEdit = !!payload.team_id;
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';

            const response = await fetch(`${baseUrl}api/teams`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const seasonId = form.querySelector('[name="season_id"]').value;
                const triggerBtns = document.querySelectorAll(`.view-teams-trigger[data-season-id="${seasonId}"]`);

                let updatedTeams = [];
                triggerBtns.forEach(btn => {
                    let teams = JSON.parse(btn.dataset.teams || '[]');

                    if (isEdit) {
                        teams = teams.map(t => {
                            if (t.team_id == result.teamData.team_id) {
                                return {
                                    ...t,
                                    ...result.teamData,
                                    player_count: result.teamData.player_count ?? t.player_count ?? 0
                                };
                            }
                            return t;
                        });
                    } else {
                        teams.push({
                            ...result.teamData,
                            player_count: result.teamData.player_count ?? 0
                        });
                    }

                    btn.dataset.teams = JSON.stringify(teams);
                    updatedTeams = teams;

                    const countText = btn.querySelector('.team-count-label') || btn.lastChild;
                    if (countText) {
                        const count = teams.length;
                        countText.textContent = ` ${count} ${count === 1 ? 'Team' : 'Teams'}`;
                    }
                });

                window.dispatchEvent(new CustomEvent('teamsUpdated', {
                    detail: {
                        seasonId: seasonId,
                        teams: updatedTeams
                    }
                }));

                apiMsg.innerHTML = `<div class="bg-green-50 text-green-700 p-3 rounded-xl font-bold text-sm">
                    Team ${isEdit ? 'Updated' : 'Added'} Successfully!
                </div>`;

                setTimeout(() => {
                    if (cancelBtn) cancelBtn.click();
                    apiMsg.innerHTML = '';
                    form.reset();

                    const idInput = form.querySelector('[name="team_id"]');
                    if (idInput) {
                        idInput.value = '';
                    }
                }, 1000);
            } else {
                apiMsg.innerHTML = (result.messages || ['Check your input']).map(msg => `
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${msg}
                    </div>
                `).join('');
            }

        } catch (err) {
            console.error('Team Submission Error:', err);
            apiMsg.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                Unexpected error. Please try again.
            </div>`;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;
            if (cancelBtn) cancelBtn.classList.remove('hidden');
        }
    });
}
