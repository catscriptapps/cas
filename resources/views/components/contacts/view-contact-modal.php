<?php
// /resources/views/components/contacts/view-contact-modal.php
?>
<div id="view-contact-modal" class="fixed inset-0 z-50 hidden">
    <div id="close-view-modal-overlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div id="view-contact-avatar" class="h-10 w-10 flex-shrink-0 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-lg">?</div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate" id="view-contact-name">Contact Name</h3>
                        <p id="view-contact-org" class="text-xs text-gray-500 dark:text-gray-400 truncate"></p>
                    </div>
                </div>
                <button type="button" class="close-view-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 flex-shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <span id="view-contact-status" class="px-3 py-1 rounded-full text-xs font-bold border"></span>
                        <span id="view-contact-emergency-badge" class="hidden px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">Emergency Priority</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5">
                    <div class="flex items-start space-x-3">
                        <div class="mt-1 text-primary-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Email Address</p>
                            <p id="view-contact-email" class="text-sm font-medium text-gray-900 dark:text-white break-all"></p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="mt-1 text-primary-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Phone Number</p>
                            <p id="view-contact-phone" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="mt-1 text-primary-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Leagues &amp; Assignments</p>
                            <p id="view-contact-leagues" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end space-x-3">
                <button type="button" class="close-view-modal px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                    Close
                </button>
                <button type="button" id="view-contact-edit-btn" class="px-4 py-2 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all active:scale-95 shadow-md">
                    Edit Contact
                </button>
            </div>
        </div>
    </div>
</div>
