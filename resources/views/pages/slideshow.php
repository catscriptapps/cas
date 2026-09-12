<?php
// /resources/views/pages/slideshow.php

declare(strict_types=1);

use App\Utils\IdEncoder;
use Src\Controller\SlideshowController;

/**
 * Admin-only management page for the home page hero's rotating background
 * images (see SlideshowController / Slide model). Route is protected via
 * NavigationConfig::getProtectedPaths() -- public/index.php's guard already
 * keeps a non-admin out before this file ever runs.
 */

$slides = (new SlideshowController())->getAll();
?>

<div class="space-y-6 max-w-6xl mx-auto py-10 font-sans">
    <?php
    $breadcrumbs = ['Slideshow' => '/slideshow'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "Slideshow" + its NavigationConfig summary.
    ?>

    <div class="flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-gray-400 font-medium max-w-md">
            Click a slide's move icon, then another slide's move icon, to swap their order. The first slide shown here plays first on the home page.
        </p>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" id="delete-selected-slides-btn" hidden
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-600 hover:bg-red-700 text-white font-black text-[11px] uppercase tracking-widest shadow-md transition-all active:scale-[0.98]">
                <i class="fa-solid fa-trash text-[10px]"></i>
                Delete Selected (<span id="selected-slides-count">0</span>)
            </button>
            <button type="button" id="add-slides-btn"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white font-black text-[11px] uppercase tracking-widest shadow-md transition-all active:scale-[0.98]">
                <i class="fa-solid fa-plus text-[10px]"></i>
                Add Images
            </button>
        </div>
    </div>

    <div id="slideshow-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($slides as $slide): ?>
            <div class="slide-card group relative rounded-2xl overflow-hidden border-2 border-transparent bg-white dark:bg-gray-900 shadow-sm aspect-video"
                data-encoded-id="<?= htmlspecialchars($slide['encoded_id']) ?>">
                <img src="<?= $assetBase . htmlspecialchars($slide['filename']) ?>" alt="Slide" class="w-full h-full object-cover">

                <label class="absolute top-2 left-2 z-10">
                    <input type="checkbox" data-select-slide class="h-5 w-5 rounded border-2 border-white shadow accent-primary-600 cursor-pointer">
                </label>

                <div class="absolute inset-x-0 bottom-0 flex items-center justify-end gap-1.5 p-2 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                    <button type="button" data-action="reorder-slide" title="Move"
                        class="h-8 w-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white backdrop-blur transition-colors">
                        <i class="fa-solid fa-arrows-up-down-left-right text-xs"></i>
                    </button>
                    <button type="button" data-action="delete-slide" title="Delete"
                        class="h-8 w-8 flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 text-white transition-colors">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($slides)): ?>
        <p id="no-slides-message" class="text-sm text-gray-400 font-medium italic text-center py-16 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl">
            No slides yet -- click "Add Images" to add the first one.
        </p>
    <?php endif; ?>
</div>
