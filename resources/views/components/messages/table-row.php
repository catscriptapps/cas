<?php
// /resources/components/messages/table-row.php

/** @var App\Models\Message $item */

// 💎 Resolve Name: Check the column first, then the relationship
$displayName = $item->full_name;
if (empty($displayName) && $item->sender) {
    $displayName = $item->sender->name ?? $item->sender->full_name;
}
$displayName = $displayName ?: 'System User';

$pageKey = 'messages';
?>

<div class="group relative flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer border-b border-gray-100 dark:border-white/5"
    data-<?= $pageKey ?>-id="<?= $item->id ?>"
    data-action="open-<?= $pageKey ?>">

    <div class="w-2.5 h-2.5 rounded-full shrink-0 <?= !$item->is_read ? 'bg-primary-600 shadow-[0_0_8px_rgba(139,92,246,0.5)]' : 'bg-transparent' ?>"></div>

    <div class="hidden sm:flex h-10 w-10 flex-shrink-0 rounded-xl bg-gray-100 dark:bg-gray-800 items-center justify-center text-gray-600 dark:text-gray-400 font-bold text-xs border border-gray-200/50 dark:border-white/5">
        <?= strtoupper(substr($displayName, 0, 1)) ?>
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between gap-2 mb-0.5">
            <h4 class="text-sm font-bold truncate <?= !$item->is_read ? 'text-gray-900 dark:text-white' : 'text-gray-500' ?>">
                <?= htmlspecialchars($displayName) ?>
            </h4>
            <span class="text-[11px] font-medium text-gray-400 whitespace-nowrap">
                <?= $item->created_at ? $item->created_at->format('M d') : '' ?>
            </span>
        </div>

        <div class="flex items-center justify-between gap-4">
            <p class="text-sm truncate <?= !$item->is_read ? 'text-gray-800 dark:text-gray-200 font-medium' : 'text-gray-400' ?>">
                <?= htmlspecialchars($item->subject ?? 'No Subject') ?>
            </p>

            <div class="opacity-0 group-hover:opacity-100 flex items-center gap-2 transition-opacity hidden sm:flex">
                <button title="Archive"
                    data-action="archive"
                    data-id="<?= $item->id ?>"
                    class="p-1 hover:text-primary-600 text-gray-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </button>

                <button title="Delete"
                    data-action="delete"
                    data-id="<?= $item->id ?>"
                    class="delete-message-btn p-1 hover:text-red-500 text-gray-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>