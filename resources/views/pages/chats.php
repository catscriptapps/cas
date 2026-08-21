<?php
// /resources/views/pages/chats.php

declare(strict_types=1);

use Src\Controller\ChatsController;
use Src\Service\AuthService;

if (AuthService::isLoggedIn()) {
    $pageKey = 'chats';
    $pageDescription = 'Manage your private conversations and community connections.';
    $controller = new ChatsController();

    // Fetch the inbox logic
    $controller->index();
    $inboxHtml = $GLOBALS['inboxHtml'] ?? '';
    $totalConversations = $GLOBALS['totalCount'] ?? 0;

    // Page Icon: Chat bubbles
    $pageIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>';
?>

    <div id="chats-landing-page" class="min-h-screen bg-gray-50 dark:bg-gray-950 font-sans pb-20 transition-colors duration-300">

        <div class="max-w-7xl mx-auto pt-12 px-4 sm:px-6 lg:px-8">

            <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6" data-aos="fade-down">
                <div class="flex items-start gap-5">
                    <div class="mt-4 hidden sm:flex w-20 h-20 rounded-[2rem] bg-gradient-to-br from-primary-500 to-secondary-600 items-center justify-center text-white shadow-2xl shadow-primary-500/20 rotate-3 hover:rotate-0 transition-transform duration-500">
                        <?= preg_replace('/(<svg[^>]*)(>)/i', '$1 class="w-10 h-10 animate-pulse"$2', $pageIcon) ?>
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 dark:bg-white/5 text-primary-600 dark:text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] mb-3 border border-primary-500/20 dark:border-white/10">
                            Secure Channel
                        </div>
                        <h1 class="text-4xl sm:text-5xl font-black text-navy-900 dark:text-white tracking-tighter leading-none uppercase">Messenger</h1>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="relative group">
                        <input type="text" id="inbox-search" placeholder="Search chats..."
                            class="pl-12 pr-6 py-3 rounded-xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-xs font-bold focus:ring-2 focus:ring-primary-500 transition-all w-64">

                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 relative">

                <div class="absolute -left-24 top-60 opacity-[0.02] dark:opacity-[0.04] pointer-events-none rotate-12 hidden xl:block">
                    <?= preg_replace('/(<svg[^>]*)(>)/i', '$1 class="w-[30rem] h-[30rem]"$2', $pageIcon) ?>
                </div>

                <aside class="lg:col-span-3 space-y-4 relative z-10" data-aos="fade-right" data-aos-delay="100">
                    <div class="bg-white dark:bg-gray-900/50 p-2 rounded-[1.5rem] border border-gray-100 dark:border-white/5">
                        <button id="btn-new-chat" class="w-full flex items-center gap-3 px-5 py-4 rounded-xl bg-primary-600 text-white shadow-lg shadow-primary-500/20 transition-all hover:scale-[1.02] active:scale-95 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 transition-transform group-hover:rotate-90 duration-300">
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 9a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25V9Z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-black uppercase tracking-widest">New Chat</span>
                        </button>
                    </div>

                    <div class="p-6 rounded-[2rem] bg-secondary-900 text-white shadow-xl hidden lg:block overflow-hidden relative border border-white/5">
                        <div class="relative z-10">
                            <span class="text-[9px] font-black uppercase tracking-widest text-primary-400">Active Conversations</span>
                            <div class="text-3xl font-black mt-1">
                                <?= $totalConversations ?>
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest ml-1">People</span>
                            </div>
                        </div>

                        <div class="absolute -right-6 -bottom-6 text-white/5 rotate-12 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-32 h-32">
                                <path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0 1 12 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 0 1-3.476.383L12 21.75l-3.674-3.675A49.477 49.477 0 0 1 4.848 17.7c-1.978-.292-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.678 3.348-3.97Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </aside>

                <main class="lg:col-span-9 relative z-10" data-aos="fade-left" data-aos-delay="200">
                    <div class="bg-white dark:bg-gray-900/50 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-white/5 overflow-hidden">

                        <div class="px-8 py-6 border-b border-gray-100 dark:border-white/5 flex items-center justify-between bg-gray-50/50 dark:bg-white/5">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Recent Threads</span>
                            <div class="flex gap-2"><!-- Placeholder for future action buttons (e.g., filter, settings) 
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-white dark:hover:bg-white/10 transition-all group">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 group-hover:text-primary-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                                    </svg>
                                </button>

                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-white dark:hover:bg-white/10 transition-all group">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 group-hover:text-primary-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                    </svg>
                                </button> -->
                            </div>
                        </div>

                        <div id="inbox-scroll-container" class="max-h-[600px] overflow-y-auto custom-scrollbar">
                            <?php if (!empty($inboxHtml)): ?>
                                <?= $inboxHtml ?>
                            <?php else: ?>
                                <div class="py-20 flex flex-col items-center justify-center text-center">
                                    <div class="w-20 h-20 bg-gray-100 dark:bg-white/5 rounded-full flex items-center justify-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-300 dark:text-gray-600">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-navy-900 dark:text-white font-bold">No active chats</h3>
                                    <p class="text-xs text-gray-500 mt-1 max-w-[200px]">Start a conversation by visiting a profile or your network.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </main>
            </div>
        </div>
    </div>

<?php
    include __DIR__ . '/../components/chats/detail-modal.php';
} else {
    include __DIR__ . '/auth-required.php';
}
