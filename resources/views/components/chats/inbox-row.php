<?php
// /resources/views/components/chats/inbox-row.php

/**
 * @var array $item (encoded_user_id, display_name, location, avatar_url, initial, last_snippet, time, unread_count)
 */
?>

<div
    data-id="<?= $item['encoded_user_id'] ?>"
    class="flex items-center p-4 transition-colors border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 active:bg-gray-100 dark:active:bg-white/10 group cursor-pointer chat-row-trigger">

    <div class="relative flex-shrink-0">
        <?php if ($item['avatar_url']): ?>
            <img src="<?= htmlspecialchars($item['avatar_url']) ?>" class="w-14 h-14 rounded-full object-cover border-2 border-white dark:border-gray-900 shadow-sm" alt="<?= $item['display_name'] ?>">
        <?php else: ?>
            <div class="flex items-center justify-center w-14 h-14 bg-secondary-900 text-white rounded-full font-black text-xl shadow-sm">
                <?= $item['initial'] ?>
            </div>
        <?php endif; ?>

        <div class="absolute bottom-0.5 right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full"></div>
    </div>

    <div class="flex-1 min-w-0 ml-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-secondary-900 dark:text-white uppercase tracking-tight truncate group-hover:text-primary-600 transition-colors">
                <?= htmlspecialchars($item['display_name']) ?>
            </h3>
            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                <?= $item['time'] ?>
            </span>
        </div>

        <div class="flex items-center gap-1 mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5 text-gray-400 opacity-70">
                <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
            </svg>
            <span class="text-[9px] text-gray-400 font-black uppercase tracking-widest truncate">
                <?= htmlspecialchars($item['location']) ?>
            </span>
        </div>

        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate pr-4 <?= $item['unread_count'] > 0 ? 'font-black text-secondary-900 dark:text-white italic' : '' ?>">
                <?= htmlspecialchars($item['last_snippet']) ?>
            </p>

            <?php if ($item['unread_count'] > 0): ?>
                <span class="flex-shrink-0 flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-black text-white bg-primary-600 rounded-full shadow-lg shadow-primary-500/20 animate-pulse">
                    <?= $item['unread_count'] ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>