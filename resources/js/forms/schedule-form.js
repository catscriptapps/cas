// /resources/js/forms/schedule-form.js

export function scheduleForm({
    mode = 'add',
    gameId = '',
    gameDate = new Date().toLocaleDateString('en-CA'),
    gameTime = '6:00 PM',
    locationId = '',
    homeTeamId = '',
    awayTeamId = '',
    ref1 = '',
    ref2 = '',
    timekeep = '',
    isPlayoff = '0',
    buttonLabel = 'Save Game',
    formId = 'schedules-form',
    locations = [],
    teams = []
}) {
    const idPrefix = mode === 'edit' ? 'sched-edit' : 'sched-add';
    const inputClasses = `block w-full rounded-xl border border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-400 focus:ring-primary-400 sm:text-sm transition-all duration-200 py-2 px-4`;
    const labelClasses = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1 ml-1";

    const parseTime = (t) => {
        if (!t || typeof t !== 'string') return { hrs: '06', mins: '00', ampm: 'PM' };
        const parts = t.split(' ');
        const clock = parts[0];
        const ampm = parts[1] || 'PM';
        const [h, m] = clock.split(':');
        return {
            hrs: String(h).padStart(2, '0'),
            mins: m ? m.substring(0, 2) : '00',
            ampm: ampm
        };
    };

    const tData = parseTime(gameTime);

    // If teams weren't passed in (Add mode), scrape the registered-teams
    // table already rendered on the page. If they were (Edit mode), use the
    // API results -- matches legacy exactly.
    let finalTeams = teams;
    if (finalTeams.length === 0) {
        const teamRows = document.querySelectorAll('#registered-teams-table .team-row');
        finalTeams = Array.from(teamRows).map(row => ({
            id: row.dataset.teamId,
            name: row.dataset.teamName
        }));
    }

    return `
    <form id="${formId}" class="w-full max-w-xl mx-auto space-y-4 p-1 font-sans" novalidate data-encoded-id="${gameId}">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-x-4 gap-y-3">

            <div class="md:col-span-7">
                <label for="${idPrefix}-date" class="${labelClasses}">Game Date</label>
                <input type="date" id="${idPrefix}-date" name="txt_game_date" value="${gameDate}" required class="${inputClasses}">
            </div>

            <div class="md:col-span-5">
                <label for="${idPrefix}-hrs" class="${labelClasses}">Time</label>
                <div class="flex space-x-1">
                    <select id="${idPrefix}-hrs" name="sel_hrs" class="${inputClasses} !px-1 text-center">
                        ${[...Array(12).keys()].map(i => {
                            const val = String(i + 1).padStart(2, '0');
                            return `<option value="${val}" ${val === tData.hrs ? 'selected' : ''}>${i + 1}</option>`;
                        }).join('')}
                    </select>
                    <select name="sel_mins" class="${inputClasses} !px-1 text-center">
                        ${['00', '15', '30', '45'].map(m => `<option value="${m}" ${m === tData.mins ? 'selected' : ''}>${m}</option>`).join('')}
                    </select>
                    <select name="sel_am_pm" class="${inputClasses} !px-1 text-center">
                        <option value="PM" ${tData.ampm === 'PM' ? 'selected' : ''}>PM</option>
                        <option value="AM" ${tData.ampm === 'AM' ? 'selected' : ''}>AM</option>
                    </select>
                </div>
            </div>

            <div class="md:col-span-12">
                <label for="${idPrefix}-location" class="${labelClasses}">Location</label>
                <select id="${idPrefix}-location" name="sel_location" required class="${inputClasses}">
                    <option value="">Select Location</option>
                    ${locations.map(l => {
                        const lId = l.location_id || l.id;
                        const lDesc = l.location_desc;
                        return `<option value="${lId}" ${String(lId) === String(locationId) ? 'selected' : ''}>${lDesc}</option>`;
                    }).join('')}
                </select>
            </div>

            <div class="md:col-span-6">
                <label for="${idPrefix}-home" class="${labelClasses}">Home Team</label>
                <select id="${idPrefix}-home" name="sel_teams_home" required class="${inputClasses}">
                    <option value="">Select Home Team</option>
                    ${finalTeams.map(t => {
                        const tId = t.team_id || t.id;
                        const tName = t.team_name || t.name;
                        return `<option value="${tId}" ${String(tId) === String(homeTeamId) ? 'selected' : ''}>${tName}</option>`;
                    }).join('')}
                </select>
            </div>
            <div class="md:col-span-6">
                <label for="${idPrefix}-away" class="${labelClasses}">Away Team</label>
                <select id="${idPrefix}-away" name="sel_teams_away" required class="${inputClasses}">
                    <option value="">Select Away Team</option>
                    ${finalTeams.map(t => {
                        const tId = t.team_id || t.id;
                        const tName = t.team_name || t.name;
                        return `<option value="${tId}" ${String(tId) === String(awayTeamId) ? 'selected' : ''}>${tName}</option>`;
                    }).join('')}
                </select>
            </div>

            <div class="md:col-span-4">
                <label for="${idPrefix}-ref1" class="${labelClasses}">Referee 1</label>
                <input type="text" id="${idPrefix}-ref1" name="txt_referee_1" value="${ref1}" class="${inputClasses}" placeholder="Name">
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-ref2" class="${labelClasses}">Referee 2</label>
                <input type="text" id="${idPrefix}-ref2" name="txt_referee_2" value="${ref2}" class="${inputClasses}" placeholder="Name">
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-tk" class="${labelClasses}">Timekeep</label>
                <input type="text" id="${idPrefix}-tk" name="txt_timekeep" value="${timekeep}" class="${inputClasses}" placeholder="Name">
            </div>

            <div class="md:col-span-12">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
                    <label for="${idPrefix}-playoff" class="text-sm font-bold text-gray-700 dark:text-gray-300">Playoff Game?</label>
                    <select id="${idPrefix}-playoff" name="sel_is_playoff" class="bg-transparent border-none text-sm font-bold text-primary-600 focus:ring-0">
                        <option value="0" ${isPlayoff === '0' ? 'selected' : ''}>No</option>
                        <option value="1" ${isPlayoff === '1' ? 'selected' : ''}>Yes</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-8 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-primary-700 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}
