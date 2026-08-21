<?php
// /resources/views/components/inspections/photo-library.php
//
// The inspection's shared photo library: upload happens here (not
// per-section any more); each card carries a checkbox per company section
// plus a "Contract" checkbox, so a photo can be assigned to any number of
// sections and/or the Contracts page. See InspectionLibraryController.

use Src\Controller\InspectionLibraryController;

/** @var \Illuminate\Support\Collection $pictures InspectionPicture rows */
/** @var \Illuminate\Support\Collection $sections */
/** @var \Illuminate\Support\Collection $assignments InspectionPictureSection rows grouped by picture_id */
/** @var array $contractIds picture ids currently assigned as contract documents */
/** @var string $assetBase */
/** @var string $encodedInspectionId */
/** @var bool $isLocked */
?>

<div id="section-content-inner" data-section-id="library-photos">
    <div class="p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between mb-1 flex-wrap gap-2">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Photo Library</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upload once, then check off which sections (and/or Contracts) each photo belongs to.</p>
                </div>
                <?php if (!$isLocked): ?>
                    <label class="inline-flex items-center gap-1.5 text-[10px] font-bold text-gray-500 dark:text-gray-400 cursor-pointer shrink-0">
                        <input type="checkbox" id="photo-library-select-all" class="w-3.5 h-3.5 text-primary-600 rounded focus:ring-primary-500">
                        Select All
                    </label>
                <?php endif; ?>
            </div>
            <?php if (!$isLocked): ?>
                <div class="flex items-center gap-2">
                    <button type="button" id="delete-selected-library-photos-btn"
                        class="hidden items-center gap-1.5 rounded-lg bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-bold text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span id="delete-selected-library-photos-label">Delete Selected</span>
                    </button>
                    <button type="button" id="trigger-photo-library-upload"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 px-3.5 py-2 text-xs font-bold text-white transition-colors shadow-sm shadow-primary-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add Photos
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div id="photo-library-grid" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php foreach ($pictures as $picture): ?>
                <?= InspectionLibraryController::renderLibraryPictureCard(
                    $picture,
                    $sections,
                    $assignments[$picture->id] ?? collect(),
                    in_array((int)$picture->id, $contractIds, true),
                    $isLocked
                ) ?>
            <?php endforeach; ?>
        </div>
        <p id="photo-library-empty" class="text-xs text-gray-400 mt-3 <?= $pictures->isNotEmpty() ? 'hidden' : '' ?>">No photos uploaded to this inspection yet.</p>
    </div>
</div>
