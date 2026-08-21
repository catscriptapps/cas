<?php
// /resources/views/pages/slideshow.php

declare(strict_types=1);
?>

<div class="space-y-6 max-w-full overflow-x-hidden py-10">
    <?php
    $breadcrumbs = ['Slideshow' => '/slideshow'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Slideshow</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Manage the rotating background images shown in the site header. Changes take effect after a page refresh.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <button type="button" id="delete-selected-slides-btn" data-tooltip="Delete Selected"
                class="hidden shrink-0 items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-red-700 transition-all active:scale-95 focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span id="delete-selected-slides-label" class="hidden xs:inline md:inline">Delete Selected</span>
            </button>

            <button type="button" id="trigger-slideshow-upload"
                class="shrink-0 flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-700 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="hidden xs:inline md:inline">Add Photos</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between pb-4 mb-2 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <h3 class="text-xs font-black text-primary-500 uppercase tracking-[0.3em] flex items-center gap-2">
                    <span class="w-8 h-[2px] bg-primary-500"></span>
                    Header Images
                </h3>
                <span id="slideshow-pics-count" class="text-[10px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-950/30 px-2 py-0.5 rounded-md border border-primary-100 dark:border-primary-900/50">0</span>
            </div>
            <p class="hidden sm:block text-[11px] text-gray-400 dark:text-gray-500 font-medium">Click a photo's move icon, then click another photo to place it there</p>
        </div>

        <div id="slideshow-pics-wrapper" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3"></div>
    </div>
</div>
