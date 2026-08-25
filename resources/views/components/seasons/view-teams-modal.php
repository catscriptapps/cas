<?php
// /resources/views/components/seasons/view-teams-modal.php
?>

<div id="view-teams-modal" class="fixed inset-0 z-50 hidden">
    <div id="close-teams-modal-overlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <?php
    // `items-start` + `overflow-y-auto` (not `items-center` with no scroll
    // escape hatch) -- on a short viewport (a big-screen TV's browser often
    // reports a surprisingly short one), a perfectly-centered modal taller
    // than the viewport had its top half pushed above the visible area with
    // no way to scroll up to it. `my-8` keeps it visually centered-ish when
    // it *does* fit, same pattern as factories/modal-factory.js.
    ?>
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto font-sans">
        <div class="bg-white dark:bg-gray-900 w-full max-w-lg my-8 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate" id="view-season-division-name">Division Name</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400" id="view-season-year-display">Season Year</p>
                    </div>
                </div>
                <button type="button" class="close-teams-modal text-gray-400 hover:text-gray-600 p-1">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div id="modal-list-view">
                    <div class="flex justify-between items-center mb-6">
                        <span id="view-season-team-count" class="text-xs font-bold text-gray-400 uppercase tracking-widest">0 Teams Registered</span>
                        <button type="button" id="show-add-team-form" class="text-xs font-bold text-primary-600 hover:text-primary-700 uppercase tracking-widest flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Team
                        </button>
                    </div>

                    <div id="teams-list-container" class="max-h-[350px] overflow-y-auto pr-2 custom-scrollbar space-y-3">
                    </div>
                </div>

                <?php include __DIR__ . '/team-form.php'; ?>
                <?php include __DIR__ . '/view-players.php'; ?>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end space-x-3">
                <button type="button" class="close-teams-modal px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded-xl transition-colors">Close</button>
            </div>
        </div>
    </div>
</div>
