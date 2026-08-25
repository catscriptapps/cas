<?php
// /resources/views/components/seasons/view-players.php
?>

<div id="player-management-view" class="hidden space-y-4">
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h4 id="player-view-team-name" class="text-sm font-bold text-secondary-600 uppercase tracking-tight">Team Name</h4>
            <p class="text-[10px] text-gray-400 font-bold uppercase">Roster Management</p>
        </div>
        <span id="view-team-player-count"
            class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider
             bg-primary-50 text-primary-700 border-primary-100
             dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/50">
            0 Players Assigned
        </span>
        <button type="button" id="back-to-teams" class="text-xs font-bold text-gray-500 hover:text-secondary-600 flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Teams
        </button>
    </div>

    <form id="add-player-form" class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-xl border border-gray-100 dark:border-gray-700 space-y-3" novalidate>
        <input type="hidden" name="team_id" id="player-form-team-id">
        <input type="hidden" name="season_id" id="player-form-season-id">

        <div>
            <label for="registration-search-select" class="block text-[10px] font-bold text-gray-400 uppercase mb-1 ml-1">Select Player from Registrations</label>
            <select required name="user_id" id="registration-search-select" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm outline-none focus:ring-2 focus:ring-secondary-500 transition-all">
                <option value="">Choose a registered player...</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <label class="flex-1 flex items-center gap-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 h-[42px] cursor-pointer hover:border-secondary-300 transition-colors">
                <input type="checkbox" name="is_goalie" id="player-is-goalie" value="1" class="w-4 h-4 rounded text-secondary-600 border-gray-300 focus:ring-secondary-500">
                <span class="text-[10px] font-bold text-gray-500 uppercase">Goalie?</span>
            </label>

            <button type="submit" id="add-player-to-roster-btn" class="bg-secondary-500 hover:bg-secondary-600 text-white font-bold px-4 rounded-lg shadow-sm shadow-secondary-100 transition-all flex items-center justify-center leading-tight text-center min-w-[100px] h-[42px]">
                <span class="text-[10px] uppercase tracking-tighter">Add to<br>Roster</span>
            </button>
        </div>
    </form>

    <div id="players-list-container" class="max-h-[300px] overflow-y-auto custom-scrollbar space-y-2 pt-2">
    </div>
</div>
