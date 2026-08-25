// /resources/js/utils/seasons/player-templates.js

export const playerRowTemplate = (player) => {
    const isGoalie = parseInt(player.is_goalie) === 1;
    const joinedDate = player.date_created ? new Date(player.date_created).toLocaleDateString() : 'N/A';

    return `
        <div class="flex items-center justify-between p-3 mb-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl hover:border-lime-200 dark:hover:border-lime-900/50 transition-colors shadow-sm group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-lime-50 dark:bg-lime-900/20 flex items-center justify-center text-lime-600 dark:text-lime-400 font-bold text-xs">
                    ${player.full_name.charAt(0)}
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">${player.full_name}</div>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Joined: ${joinedDate}</span>

                        <button type="button"
                                data-player-id="${player.player_id}"
                                data-is-goalie="${isGoalie}"
                                class="toggle-goalie-btn text-[9px] px-1.5 py-0.5 rounded font-bold uppercase border transition-all ${
                                    isGoalie
                                    ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 hover:border-red-100 dark:hover:border-red-800'
                                    : 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-100 dark:hover:border-blue-800'
                                }"
                                title="${isGoalie ? 'Remove Goalie Status' : 'Make Goalie'}">
                            ${isGoalie ? 'Goalie' : '+ Goalie'}
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative flex items-center justify-end min-w-[60px]">
                <button
                    type="button"
                    class="player-delete-init p-2 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                    title="Remove from roster"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>

                <div class="player-delete-confirm hidden flex items-center gap-1 animate-in fade-in slide-in-from-right-2 duration-200">
                    <button type="button" class="player-delete-cancel p-1.5 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="Cancel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <button type="button" data-player-id="${player.player_id}" class="player-delete-confirm-btn p-1.5 text-emerald-500 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors" title="Confirm Removal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
};
