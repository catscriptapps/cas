<?php
// /resources/views/components/messages/view-message-modal.php
?>
<?php
// z-[10000] (not z-50) on both layers -- the fixed topbar is z-[9999] (see
// layout-topbar.php); a modal at z-50 would render behind it. Matches
// incident-reports/view-report-modal.php and friends.
?>
<div id="view-message-modal" class="fixed inset-0 z-[10000] hidden">
    <div id="close-view-message-overlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-[10000] flex items-start justify-center p-4 overflow-y-auto font-sans">
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl my-8 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="h-10 w-10 flex-shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate" id="view-message-subject">Message</h3>
                        <p id="view-message-meta" class="text-xs text-gray-500 dark:text-gray-400 font-sans truncate"></p>
                    </div>
                </div>
                <button type="button" class="close-view-message text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Received</p>
                    <p id="view-message-date" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                </div>

                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Message</p>
                    <div id="view-message-body" class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap border-l-4 border-primary-500 font-sans"></div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex flex-wrap justify-end gap-3">
                <button type="button" class="close-view-message px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Close</button>
                <button type="button" id="view-message-archive-btn" class="px-4 py-2 text-sm font-bold text-white bg-secondary-600 hover:bg-secondary-700 rounded-xl transition-all active:scale-95 shadow-md">Archive</button>
                <button type="button" id="view-message-delete-btn" class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all active:scale-95 shadow-md">Delete</button>
            </div>
        </div>
    </div>
</div>
