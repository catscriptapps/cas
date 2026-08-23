<?php
// /resources/views/components/registrations/view-registration-modal.php

use Src\Service\AuthService;

$isLoggedIn = AuthService::isLoggedIn();
?>

<div id="view-registration-modal" class="fixed inset-0 z-[10000] hidden">
    <div id="close-view-registration-overlay" class="fixed inset-0 bg-navy-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200 mt-20 mb-8">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="h-12 w-12 flex-shrink-0 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-xl" id="view-reg-avatar-fallback">?</div>
                    <div>
                        <h3 class="text-lg font-bold text-navy-900 dark:text-white truncate" id="view-reg-name">Registrant Name</h3>
                        <p id="view-reg-email" class="text-xs text-gray-500 dark:text-gray-400 font-medium"></p>
                    </div>
                </div>
                <button type="button" class="close-view-registration text-gray-400 hover:text-navy-600 dark:hover:text-gray-200 p-1 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-8 space-y-6 font-sans">
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span id="view-reg-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border"></span>
                        <span id="view-reg-paid-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border"></span>
                    </div>
                    <span id="view-reg-joined" class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Division</p>
                        <p id="view-reg-division" class="text-sm font-bold text-navy-900 dark:text-white mt-1"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Amount Paid</p>
                        <p id="view-reg-amount" class="text-sm font-bold text-navy-900 dark:text-white mt-1"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Phone</p>
                        <p id="view-reg-phone" class="text-sm font-bold text-navy-900 dark:text-white mt-1"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Location</p>
                        <p id="view-reg-location" class="text-sm font-bold text-navy-900 dark:text-white mt-1"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Desired Position</p>
                        <p id="view-reg-position" class="text-sm font-bold text-navy-900 dark:text-white mt-1"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Heard About Us Via</p>
                        <p id="view-reg-source" class="text-sm font-bold text-navy-900 dark:text-white mt-1"></p>
                    </div>
                </div>

                <div id="view-reg-notes-wrapper" class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Special Requests</p>
                    <p id="view-reg-notes" class="text-sm text-navy-900 dark:text-white"></p>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end space-x-3">
                <button type="button" class="close-view-registration px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-navy-900 dark:hover:text-white transition-colors">
                    Close
                </button>
                <?php if ($isLoggedIn): ?>
                    <button type="button" id="view-registration-edit-btn" class="px-6 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all active:scale-95 shadow-lg shadow-primary-600/20">
                        Edit Registration
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
