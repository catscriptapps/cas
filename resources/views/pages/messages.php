<?php
// /resources/views/pages/messages.php

declare(strict_types=1);

use Src\Controller\MessagesController;
use Src\Service\AuthService;

if (AuthService::isLoggedIn() && AuthService::isAdmin()) {
    $pageKey = 'messages';
    $pageTitle = 'Admin Messages';
    $pageDescription = 'Manage site-wide inquiries, sent communications, and archived records.';
    $controllerClass = MessagesController::class;

    // RESTORED: Standard Admin Folders required by sidebar-nav.php
    $folders = [
        'inbox'    => ['label' => 'Inbox', 'icon' => 'M20 13V5a2 2 0 00-2-2H6a2 2 0 00-2-2v8m16 0h-5m5 0l-1.5 1.5M11 13H4m7 0l1.5 1.5M4 13l1.5-1.5'],
        'unread'   => ['label' => 'Unread', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        'sent'     => ['label' => 'Sent', 'icon' => 'M3 10l9-7 9 7-9 7-9-7zm0 0v8a2 2 0 002 2h14a2 2 0 002-2v-8'],
        'archived' => ['label' => 'Archived', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4']
    ];

    $currentFolder = $_GET['folder'] ?? 'inbox';
    $perPage = 50;
    $items = MessagesController::paginate($currentFolder, $perPage);

    // Page Icon: Chat/Envelopes for the new Hero look
    $pageIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>';
?>

    <div id="admin-messages-page" class="min-h-screen bg-gray-50 dark:bg-gray-950 font-sans pb-20 transition-colors duration-300">

        <div class="max-w-7xl mx-auto pt-12 px-4 sm:px-6 lg:px-8">

            <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6" data-aos="fade-down">
                <div class="flex items-start gap-5">
                    <div class="hidden sm:flex w-20 h-20 rounded-[2rem] bg-gradient-to-br from-primary-800 to-black items-center justify-center text-white shadow-2xl shadow-secondary-900/20 -rotate-3 hover:rotate-0 transition-transform duration-500 border border-white/10">
                        <?= preg_replace('/(<svg[^>]*)(>)/i', '$1 class="w-10 h-10 animate-pulse"$2', $pageIcon) ?>
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-900/10 dark:bg-white/5 text-secondary-900 dark:text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] mb-3 border border-secondary-900/20 dark:border-white/10">
                            Admin Protocol
                        </div>
                        <h1 class="text-4xl sm:text-5xl font-black text-secondary-900 dark:text-white tracking-tighter leading-none uppercase">Messages</h1>
                    </div>
                </div>

                <div class="hidden gap-4">
                    <button class="px-6 py-3 rounded-xl bg-white dark:bg-white/5 text-gray-500 text-xs font-black uppercase border border-gray-200 dark:border-white/10 hover:bg-gray-50 transition-all">Export Logs</button>
                    <button class="px-6 py-3 rounded-xl bg-primary-600 text-white text-xs font-black uppercase shadow-lg shadow-primary-500/30 hover:bg-primary-500 transition-all flex items-center gap-2">
                        <i class="bi bi-plus-lg"></i> Compose
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 relative">
                <div class="absolute -left-24 top-60 opacity-[0.02] dark:opacity-[0.04] pointer-events-none rotate-12 hidden xl:block">
                    <?= preg_replace('/(<svg[^>]*)(>)/i', '$1 class="w-[30rem] h-[30rem]"$2', $pageIcon) ?>
                </div>

                <aside class="lg:col-span-3 space-y-2 relative z-10" data-aos="fade-right" data-aos-delay="100">
                    <?php include __DIR__ . '/../components/messages/sidebar-nav.php'; ?>

                    <div class="mt-8 p-6 rounded-[1rem] bg-secondary-900 text-white shadow-xl hidden lg:block">
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">Database Context</span>
                        <div class="text-2xl font-black mt-1"><?= count($items) ?> <span class="text-[10px] text-primary-500 font-bold uppercase tracking-widest ml-1">Entries</span></div>
                    </div>
                </aside>

                <main class="lg:col-span-9 relative z-10" data-aos="fade-left" data-aos-delay="200">
                    <div class="bg-white dark:bg-gray-900/50 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-white/5 overflow-hidden">
                        <?php include __DIR__ . '/../components/messages/list.php'; ?>
                    </div>
                </main>
            </div>
        </div>
    </div>

<?php
    include __DIR__ . '/../components/messages/slide-over.php';
} else {
    if (!AuthService::isLoggedIn()) {
        include __DIR__ . '/auth-required.php';
    } else {
        include __DIR__ . '/access-denied.php';
    }
}
