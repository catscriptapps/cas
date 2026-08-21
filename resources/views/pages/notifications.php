<?php
// /resources/views/pages/notifications.php

declare(strict_types=1);

use Src\Controller\NotificationsController;
use Src\Service\AuthService;

$account = AuthService::currentAccountContext();

if (!$account) {
    include __DIR__ . '/auth-required.php';
    return;
}

$notifications = NotificationsController::getLatest($account['type'], $account['id'], 50);
$unreadCount = NotificationsController::getUnreadCount($account['type'], $account['id']);

$breadcrumbs = ['Notifications' => ''];
?>

<div class="max-w-3xl mx-auto py-10 px-4 space-y-8">
    <?php include __DIR__ . '/../components/ui/breadcrumbs.php'; ?>

    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                Notifications<?= $unreadCount > 0 ? " <span class=\"text-primary-500\">({$unreadCount})</span>" : '' ?>
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Updates about your account and activity.</p>
        </div>
        <?php if ($unreadCount > 0): ?>
            <button type="button" id="mark-all-read-btn"
                class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                Mark All Read
            </button>
        <?php endif; ?>
    </div>

    <div id="notifications-list" class="space-y-3">
        <?php if ($notifications->isEmpty()): ?>
            <div class="flex flex-col items-center justify-center text-center py-16 bg-white dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                <svg class="h-12 w-12 text-gray-300 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <p class="font-bold text-gray-500 dark:text-gray-400">You're all caught up</p>
                <p class="text-xs text-gray-400 mt-1">Nothing here yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $note): ?>
                <a href="<?= $note->link ? htmlspecialchars($baseUrl . ltrim($note->link, '/')) : '#' ?>" data-partial
                    class="notification-row block bg-white dark:bg-gray-900 border <?= $note->is_read ? 'border-gray-100 dark:border-gray-800 opacity-70' : 'border-primary-200 dark:border-primary-900/50' ?> rounded-2xl p-5 shadow-sm hover:shadow-md transition-all"
                    data-id="<?= $note->id ?>" data-is-read="<?= $note->is_read ? '1' : '0' ?>">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <?php if (!$note->is_read): ?>
                                    <span class="w-2 h-2 rounded-full bg-primary-500 shrink-0"></span>
                                <?php endif; ?>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white truncate"><?= htmlspecialchars($note->subject) ?></h3>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($note->message) ?></p>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider shrink-0">
                            <?= $note->date_created ? $note->date_created->format('M j, Y') : '' ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
