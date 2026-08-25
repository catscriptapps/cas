<?php
// /resources/views/components/incident-reports/view-report-modal.php
?>
<?php
// z-[10000] (not z-50) on both layers -- the fixed topbar is z-[9999] (see
// layout-topbar.php); a modal at z-50 would render behind it. Matches
// users/view-user-modal.php and registrations/view-registration-modal.php.
?>
<div id="view-report-modal" class="fixed inset-0 z-[10000] hidden">
    <div id="close-view-modal-overlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-[10000] flex items-start justify-center p-4 overflow-y-auto font-sans">
        <div class="bg-white dark:bg-gray-900 w-full max-w-3xl my-8 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="h-10 w-10 flex-shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate" id="view-report-title">Incident Details</h3>
                        <p id="view-report-subtitle" class="text-xs text-gray-500 dark:text-gray-400 font-sans"></p>
                    </div>
                </div>
                <button type="button" class="close-view-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto custom-scrollbar">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-100 dark:border-gray-800">
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Teams Involved</p>
                            <p id="view-report-teams" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Persons Involved</p>
                            <p id="view-report-persons" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Officials / Referees</p>
                            <p id="view-report-officials" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Timekeeper</p>
                            <p id="view-report-timekeeper" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Description of Incident</p>
                        <div id="view-report-description" class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl text-sm text-gray-700 dark:text-gray-300 leading-relaxed italic border-l-4 border-primary-500 font-sans"></div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Equipment Worn</p>
                        <p id="view-report-equipment" class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-100 dark:border-blue-900/30">
                        <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Referee Outcome</p>
                        <p id="view-report-outcome" class="text-sm text-gray-800 dark:text-gray-200"></p>
                    </div>
                    <div class="p-4 bg-red-50/50 dark:bg-red-900/10 rounded-xl border border-red-100 dark:border-red-900/30">
                        <p class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase tracking-widest mb-1">Medical Assistance</p>
                        <p id="view-report-medical" class="text-sm text-gray-800 dark:text-gray-200"></p>
                    </div>
                </div>

                <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 dark:border-gray-800">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Manager on Duty</p>
                        <p id="view-report-manager-name" class="text-sm font-bold text-gray-700 dark:text-gray-300"></p>
                        <p id="view-report-manager-time" class="text-[10px] text-gray-500 italic"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</p>
                        <span id="view-report-status"></span>
                    </div>
                </div>

                <div class="pt-4 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 border-t border-gray-100 dark:border-gray-800 pt-6">
                    <p>Electronic Signature: <span id="view-report-signature" class="font-bold text-primary-600 dark:text-primary-400 underline decoration-dotted"></span></p>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end space-x-3">
                <button type="button" class="close-view-modal px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">Close</button>
                <button type="button" id="view-report-edit-btn" class="px-4 py-2 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all active:scale-95 shadow-md">Edit Report</button>
            </div>
        </div>
    </div>
</div>
