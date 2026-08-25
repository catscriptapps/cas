// /resources/js/utils/seasons/team-templates.js

export const formatDate = (dateStr) => {
    if (!dateStr || dateStr === 'N/A') return 'N/A';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    } catch (e) { return dateStr; }
};

export const teamItemTemplate = (team) => {
    const teamName = team.team_name || 'Unnamed Team';
    const teamNum = team.team_number ? `<span class="text-primary-600">#${team.team_number}</span>` : '';
    const repName = team.rep_name && team.rep_name !== 'N/A' ? team.rep_name : null;
    const createdDate = formatDate(team.date_created);

    const teamDataString = encodeURIComponent(JSON.stringify(team));

    return `
    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 hover:border-primary-200 transition-all group relative">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-3">
                <div class="h-8 w-8 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-primary-600 font-bold text-xs shadow-sm group-hover:bg-primary-600 group-hover:text-white transition-all">
                    ${teamName.charAt(0).toUpperCase()}
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        ${teamName} ${teamNum}
                    </div>
                    <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tight">
                        Group: ${team.team_group || 'N/A'}
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right">
                     <div class="text-[10px] font-bold text-gray-400 uppercase">Created</div>
                     <div class="text-[10px] font-medium text-gray-600">${createdDate}</div>
                </div>

                <div class="relative flex items-center bg-gray-100 dark:bg-gray-700/50 rounded-lg p-1">
                    <button type="button"
                            class="team-players-trigger relative p-1.5 text-gray-400 hover:text-blue-500 transition-colors"
                            data-team-id="${team.team_id}"
                            data-team-name="${teamName}"
                            title="Manage Players">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span id="badge-count-${team.team_id}"
                              class="absolute -top-1.5 -right-1.5 flex items-center justify-center min-w-[16px] h-4 px-1 bg-primary-600 text-white text-[9px] font-black rounded-full border border-white shadow-sm transition-all">
                            ${team.player_count || 0}
                        </span>
                    </button>

                    <button type="button"
                            class="team-edit-trigger p-1.5 text-gray-400 hover:text-primary-600 transition-colors"
                            data-team-encoded="${teamDataString}"
                            title="Edit Team">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>

                    <button type="button" class="team-delete-init p-1.5 text-gray-400 hover:text-red-500 transition-colors" title="Delete Team">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>

                    <div class="team-delete-confirm hidden flex items-center space-x-1 animate-in fade-in zoom-in duration-200">
                        <button type="button" class="team-delete-cancel p-1 text-gray-400 hover:text-gray-600 dark:hover:text-white"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        <button type="button" class="team-delete-confirm-btn p-1 text-red-500 hover:text-red-700" data-team-id="${team.team_id}"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></button>
                    </div>
                </div>
            </div>
        </div>
        ${repName ? `
        <div class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Representative:</span>
            <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300">${repName}</span>
        </div>` : ''}
    </div>`;
};
