<?php
// /resources/views/components/users/view-user-modal.php

use Src\Service\AuthService;

$isAdmin = AuthService::isLoggedIn();
?>

<div id="view-user-modal" class="fixed inset-0 z-[10000] hidden">
    <div id="close-view-modal-overlay" class="fixed inset-0 bg-navy-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200 mt-20 mb-8">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div id="view-user-avatar-container" class="h-12 w-12 flex-shrink-0">
                        <div id="view-user-avatar-fallback" class="h-full w-full rounded-full bg-orange-600 flex items-center justify-center text-white font-bold text-xl">?</div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy-900 dark:text-white truncate" id="view-user-name">User Name</h3>
                        <p id="view-user-email-sub" class="text-xs text-gray-500 dark:text-gray-400 font-medium"></p>
                    </div>
                </div>
                <button type="button" class="close-view-modal text-gray-400 hover:text-navy-600 dark:hover:text-gray-200 p-1 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-8 space-y-8 font-sans">
                <div class="flex justify-between items-center">
                    <div id="view-user-status-container">
                        <span id="view-user-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border"></span>
                    </div>
                    <span id="view-user-joined" class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight"></span>
                </div>

                <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                    <div class="text-orange-500 shrink-0">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Location</p>
                        <p id="view-user-combined-location" class="text-sm font-bold text-navy-900 dark:text-white"></p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="text-navy-600 dark:text-navy-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-navy-900 dark:text-white uppercase tracking-wider">Role / Account Type</h4>
                    </div>

                    <div id="view-user-roles-container" class="flex flex-wrap gap-2">
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end space-x-3">
                <button type="button" class="close-view-modal px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-navy-900 dark:hover:text-white transition-colors">
                    Close
                </button>
                <?php if ($isAdmin): ?>
                    <button type="button" id="view-user-edit-btn" class="px-6 py-2.5 text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-all active:scale-95 shadow-lg shadow-orange-600/20">
                        Edit Profile
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>