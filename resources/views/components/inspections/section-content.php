<?php
// /resources/views/components/inspections/section-content.php

/** @var \Illuminate\Support\Collection $questions */
/** @var \Illuminate\Support\Collection $answers */
/** @var \Illuminate\Support\Collection $selectedOptionsByQuestion */
/** @var \Illuminate\Support\Collection $fieldResponsesByQuestion */
/** @var \App\Models\InspectionSectionComment|null $sectionComment */
/** @var \Illuminate\Support\Collection $pictureLinks InspectionPictureSection rows, picture eager-loaded */
/** @var \Illuminate\Support\Collection $videoLinks InspectionVideoSection rows, video eager-loaded */
/** @var string $encodedInspectionId */
/** @var string $encodedSectionId */
/** @var string $assetBase */
/** @var bool $isLocked */
/** @var \App\Models\Inspection $inspection */

$isLocked = $isLocked ?? false;
?>

<div id="section-content-inner" data-section-id="<?= $encodedSectionId ?>">
    <?php if ($questions->isEmpty()): ?>
        <div class="text-center py-16 text-gray-400 dark:text-gray-500">
            <p class="font-bold text-sm">No questions in this section yet.</p>
            <p class="text-xs mt-1">Add questions to this section from the Questions page.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4" id="inspection-questions-list">
            <?php foreach ($questions as $question): ?>
                <?php include __DIR__ . '/question-answer-row.php'; ?>
            <?php endforeach; ?>
        </div>

        <?php include __DIR__ . '/section-footer.php'; ?>
    <?php endif; ?>

    <div class="mt-6 p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Section Comments</h3>
            <?php if (!$isLocked): $hintType = 'section_comment';
                $hintSectionId = $encodedSectionId;
                include __DIR__ . '/../ui/hint-trigger-button.php';
            endif; ?>
        </div>
        <textarea id="section-comment-textarea" rows="3" placeholder="Overall comments for this section..." <?= $isLocked ? 'readonly' : '' ?>
            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 text-gray-900 dark:text-white text-sm py-2.5 px-3 placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 resize-y"><?= htmlspecialchars($sectionComment->comments ?? '') ?></textarea>
        <span id="section-comment-save-indicator" class="hidden mt-1.5 text-[10px] font-bold text-green-600 dark:text-green-400">Saved</span>
    </div>

    <div class="mt-6 p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div class="flex items-center gap-3">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Photos</h3>
                <?php if (!$isLocked): ?>
                    <label class="inline-flex items-center gap-1.5 text-[10px] font-bold text-gray-500 dark:text-gray-400 cursor-pointer">
                        <input type="checkbox" id="inspection-photos-select-all" class="w-3.5 h-3.5 text-primary-600 rounded focus:ring-primary-500">
                        Select All
                    </label>
                <?php endif; ?>
            </div>
            <?php if (!$isLocked): ?>
                <div class="flex items-center gap-2">
                    <button type="button" id="delete-selected-photos-btn"
                        class="hidden items-center gap-1.5 rounded-lg bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-bold text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span id="delete-selected-photos-label">Remove Selected</span>
                    </button>
                    <button type="button" data-action="switch-to-library" data-tab="library-photos"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-600 hover:text-primary-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add from Photo Library
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <div id="inspection-photos-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php foreach ($pictureLinks as $link): ?>
                <?= \Src\Controller\InspectionLibraryController::renderSectionPictureCard($link, $isLocked) ?>
            <?php endforeach; ?>
        </div>
        <p id="inspection-photos-empty" class="text-xs text-gray-400 <?= $pictureLinks->isNotEmpty() ? 'hidden' : '' ?>">No photos assigned to this section yet. Assign some from the Photo Library.</p>
    </div>

    <div class="mt-6 p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div class="flex items-center gap-3">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Section Videos</h3>
                <?php if (!$isLocked): ?>
                    <label class="inline-flex items-center gap-1.5 text-[10px] font-bold text-gray-500 dark:text-gray-400 cursor-pointer">
                        <input type="checkbox" id="inspection-videos-select-all" class="w-3.5 h-3.5 text-primary-600 rounded focus:ring-primary-500">
                        Select All
                    </label>
                <?php endif; ?>
            </div>
            <?php if (!$isLocked): ?>
                <div class="flex items-center gap-2">
                    <button type="button" id="delete-selected-videos-btn"
                        class="hidden items-center gap-1.5 rounded-lg bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-bold text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span id="delete-selected-videos-label">Remove Selected</span>
                    </button>
                    <button type="button" data-action="switch-to-library" data-tab="library-videos"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-600 hover:text-primary-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add from Video Library
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <div id="inspection-videos-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php foreach ($videoLinks as $link): ?>
                <?= \Src\Controller\InspectionLibraryController::renderSectionVideoCard($link, $isLocked) ?>
            <?php endforeach; ?>
        </div>
        <p id="inspection-videos-empty" class="text-xs text-gray-400 <?= $videoLinks->isNotEmpty() ? 'hidden' : '' ?>">No videos assigned to this section yet. Assign some from the Video Library.</p>
    </div>
</div>
