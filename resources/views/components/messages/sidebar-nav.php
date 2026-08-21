<?php
// /resources/components/messages/sidebar-nav.php

declare(strict_types=1);

use Src\Controller\MessagesController;

/** @var array<string, array{icon: string, label: string}> $folders */
/** @var string $currentFolder */
$unreadCount = MessagesController::getUnreadCount();
?>

<nav class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-2 sticky top-6">
    <?php foreach ($folders as $key => $data): ?>
        <?php $isActive = ($currentFolder === $key); ?>
        <a href="?folder=<?= $key ?>"
            class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold transition-all group
            <?= $isActive ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' ?>">

            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 <?= $isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $data['icon'] ?>" />
                </svg>
                <?= htmlspecialchars($data['label']) ?>
            </div>

            <?php if ($key === 'unread' && $unreadCount > 0): ?>
                <span id="unread-count-badge" class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full">
                    New
                </span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>