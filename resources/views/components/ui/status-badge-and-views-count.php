<?php
// /resources/views/components/ui/status-badge-and-views-count.php

/** 
 * @var string $statusBadge
 * @var string $viewsCountId
 * @var array  $item
 */
?>

<div class="flex items-center gap-2">
    <div class="flex-shrink-0 status-badge-wrapper">
        <?= $statusBadge ?>
    </div>

    <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-secondary-50 dark:bg-secondary-900/20 border border-secondary-100 dark:border-gray-800">
        <svg class="w-3 h-3 md:w-3.5 md:h-3.5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span id="<?= $viewsCountId ?>" class="text-[10px] font-black text-secondary-600 dark:text-gray-400 uppercase tracking-wider">
            <?= number_format((int)($item['views'] ?? 0)) ?> VIEWS
        </span>
    </div>
</div>