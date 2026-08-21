<?php
// /resources/views/components/companies/view-company-modal.php

use Src\Service\AuthService;

$isAdmin = AuthService::isAdmin();
// Same rule as the row-level actions: a Company Admin only ever views their
// own company here, so they may edit it -- deleting stays Admin-only.
$canEdit = $isAdmin || AuthService::isCompanyAdmin();
?>

<div id="view-company-modal" class="fixed inset-0 z-[10000] hidden">
    <div id="close-view-company-modal-overlay" class="fixed inset-0 bg-navy-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 flex items-start justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200 mt-20 mb-8">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div id="view-company-avatar-container" data-action="view-logo" data-img-src=""
                        class="h-12 w-12 flex-shrink-0 rounded-full overflow-hidden bg-orange-600 flex items-center justify-center">
                        <span id="view-company-avatar-fallback" class="text-white font-bold text-xl">?</span>
                        <img id="view-company-header-logo-img" alt="" class="hidden w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy-900 dark:text-white truncate" id="view-company-name">Company Name</h3>
                        <p id="view-company-email-sub" class="text-xs text-gray-500 dark:text-gray-400 font-medium"></p>
                    </div>
                </div>
                <button type="button" class="close-view-company-modal text-gray-400 hover:text-navy-600 dark:hover:text-gray-200 p-1 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-8 space-y-8 font-sans">
                <div class="flex justify-between items-center">
                    <div id="view-company-status-container">
                        <span id="view-company-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border"></span>
                    </div>
                    <span id="view-company-joined" class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight"></span>
                </div>

                <div id="view-company-slogan" class="hidden text-sm italic text-gray-500 dark:text-gray-400 border-l-4 border-primary-200 dark:border-primary-800/40 pl-4"></div>

                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="text-navy-600 dark:text-navy-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h.01M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-navy-900 dark:text-white uppercase tracking-wider">Company Logo</h4>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative shrink-0 group">
                            <div id="view-company-logo-container" data-action="view-logo" data-img-src=""
                                class="h-20 w-20 rounded-2xl overflow-hidden ring-2 ring-gray-100 dark:ring-white/5 bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center transition-transform group-hover:scale-105">
                                <span id="view-company-logo-fallback" class="text-2xl font-black text-white">?</span>
                                <img id="view-company-logo-img" alt="" class="hidden w-full h-full object-cover">
                            </div>
                            <button type="button" id="view-company-logo-upload-btn" data-action="upload-logo" title="Upload logo"
                                class="absolute -bottom-1.5 -right-1.5 p-2 bg-primary-500 text-white rounded-xl shadow-lg hover:bg-primary-400 transition-all border-2 border-white dark:border-gray-900">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" stroke-width="2.5" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Click the logo to preview it full-size, or use the pencil icon to upload a new one.
                            </p>
                            <button type="button" id="view-company-logo-delete-btn" class="hidden mt-2 text-xs font-bold text-red-500 hover:text-red-600 inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove Logo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-orange-500 shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Location</p>
                            <p id="view-company-combined-location" class="text-sm font-bold text-navy-900 dark:text-white"></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-orange-500 shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Phone</p>
                            <p id="view-company-phone" class="text-sm font-bold text-navy-900 dark:text-white"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="text-navy-600 dark:text-navy-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-navy-900 dark:text-white uppercase tracking-wider">Website & Contact</h4>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span id="view-company-website" class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700"></span>
                        <span id="view-company-toll-free" class="hidden inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700"></span>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="text-navy-600 dark:text-navy-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h4 class="text-xs font-bold text-navy-900 dark:text-white uppercase tracking-wider">Company Admins</h4>
                        </div>
                        <button type="button" id="view-company-add-admin-toggle"
                            class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Admin
                        </button>
                    </div>

                    <div id="view-company-admins-list" class="space-y-2"></div>

                    <form id="view-company-add-admin-form" class="hidden mt-4 space-y-3 p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30" novalidate>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="firstName" placeholder="First Name" required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 text-sm transition-all py-2 px-3">
                            <input type="text" name="lastName" placeholder="Last Name" required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 text-sm transition-all py-2 px-3">
                        </div>
                        <input type="email" name="email" placeholder="admin@company.com" required
                            class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 text-sm transition-all py-2 px-3">
                        <div class="grid grid-cols-2 gap-3">
                            <input type="password" name="password" placeholder="Password" required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 text-sm transition-all py-2 px-3">
                            <input type="password" name="passwordConfirmation" placeholder="Confirm Password" required
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 text-sm transition-all py-2 px-3">
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" id="view-company-add-admin-cancel" class="px-4 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-all active:scale-95 shadow-md shadow-primary-500/20">
                                Add Admin
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end space-x-3">
                <button type="button" class="close-view-company-modal px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-navy-900 dark:hover:text-white transition-colors">
                    Close
                </button>
                <?php if ($canEdit): ?>
                    <button type="button" id="view-company-edit-btn" class="px-6 py-2.5 text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl transition-all active:scale-95 shadow-lg shadow-orange-600/20">
                        Edit Company
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
