<?php
// /resources/views/components/inspections/photo-card.php

use App\Models\InspectionPicture;
use App\Utils\IdEncoder;

/** @var InspectionPicture $picture */
/** @var string $assetBase */
/** @var bool $isLocked */
/** @var string $mode 'section' (a section's photo grid), 'library' (the Photo Library grid), or 'contract' (the Contracts grid) */
/** @var string|null $caption Section/contract-scoped caption (unused in 'library' mode, which has no caption of its own) */
/** @var string $sectionCheckboxesHtml Pre-rendered per-section (+ contract) checkbox pills -- 'library' mode only */

$encodedId = IdEncoder::encode((int)$picture->id);
$src = $assetBase . 'images/uploads/inspections/' . $picture->inspection_id . '/' . $picture->filename;
$isLocked = $isLocked ?? false;
$mode = $mode ?? 'section';
$caption = $caption ?? '';
$sectionCheckboxesHtml = $sectionCheckboxesHtml ?? '';

$removeTitle = match ($mode) {
    'section' => 'Remove from this section (still in the library)',
    'contract' => 'Remove from Contracts (still in the library)',
    default => 'Delete from library (removes it everywhere)',
};
$removeAction = match ($mode) {
    'section' => 'unassign-picture-section',
    'contract' => 'unassign-picture-contract',
    default => 'delete-picture-library',
};
?>
<div class="inspection-photo-card group relative rounded-xl overflow-hidden border-4 border-transparent bg-gray-100 dark:bg-gray-800 aspect-square"
    data-picture-id="<?= $encodedId ?>" data-mode="<?= $mode ?>">
    <img src="<?= htmlspecialchars($src) ?>" data-img-src="<?= htmlspecialchars($src) ?>" alt="Inspection photo"
        class="w-full h-full object-cover cursor-pointer">

    <?php if (!$isLocked): ?>
        <div class="absolute top-2 left-2 z-10 flex items-center gap-1.5">
            <label class="flex items-center justify-center w-6 h-6 rounded-md bg-white/80 dark:bg-gray-900/80 border border-gray-300 dark:border-gray-700 cursor-pointer shadow-sm">
                <input type="checkbox" data-action="select-picture" data-id="<?= $encodedId ?>" class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
            </label>
            <?php if ($mode !== 'library'): ?>
                <button type="button" data-action="reorder-picture" data-id="<?= $encodedId ?>" title="Click to move this photo, then click another to place it there"
                    class="p-1.5 rounded-md bg-black/50 text-white hover:bg-black/70 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </button>
            <?php endif; ?>
        </div>

        <button type="button" data-action="<?= $removeAction ?>" data-id="<?= $encodedId ?>" title="<?= htmlspecialchars($removeTitle) ?>"
            class="absolute top-2 right-2 p-1.5 rounded-md bg-black/50 text-white hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100">
            <?php if ($mode === 'library'): ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            <?php else: ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            <?php endif; ?>
        </button>
    <?php endif; ?>

    <?php if ($mode === 'library'): ?>
        <div class="absolute inset-x-0 bottom-0 bg-black/75 backdrop-blur-sm px-2 py-1.5 max-h-[45%] overflow-y-auto">
            <div class="flex flex-wrap gap-1"><?= $sectionCheckboxesHtml ?></div>
        </div>
    <?php else: ?>
        <input type="text" data-role="picture-description" placeholder="Add a caption..." <?= $isLocked ? 'readonly' : '' ?>
            value="<?= htmlspecialchars($caption ?? '') ?>"
            class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[11px] py-1.5 px-2 placeholder:text-white/60 border-0 focus:ring-2 focus:ring-primary-400 focus:bg-black/80">
    <?php endif; ?>
</div>
