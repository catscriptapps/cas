<?php
// /resources/views/components/standards/standard-card.php

/** @var \App\Models\Standard $page */
/** @var int $pageNumber */
/** @var string $encodedId set by StandardsController::renderCard() before including this file */
?>

<div id="standard-page-<?= (int)$page->id ?>" data-standard-id="<?= (int)$page->id ?>" data-encoded-id="<?= $encodedId ?>"
    class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-sm border-4 border-transparent ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden transition-colors">

    <div class="flex items-center justify-between px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
        <span data-page-number-label class="text-xs font-black text-primary-500 uppercase tracking-widest">Page <?= (int)$pageNumber ?></span>

        <div class="flex items-center gap-1">
            <button type="button" data-action="reorder-page" data-id="<?= $encodedId ?>" title="Click to move this page, then click another page to place it there"
                class="reorder-btn p-1.5 rounded-md text-gray-400 dark:text-gray-500 hover:bg-secondary-50 hover:text-secondary-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </button>
            <button type="button" data-action="edit-page" data-id="<?= $encodedId ?>" title="Edit Page"
                class="p-1.5 rounded-md text-gray-400 dark:text-gray-500 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button type="button" data-action="delete-page" data-id="<?= $encodedId ?>" title="Delete Page"
                class="p-1.5 rounded-md text-gray-400 dark:text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>

    <div class="max-h-96 overflow-y-auto px-6 py-5">
        <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
            <?= $page->content ?>
        </div>
    </div>
</div>
