<?php
// /resources/views/components/inspections/contracts-content.php
//
// Shows the photos currently assigned as contract documents (assigned from
// the Photo Library's "Contract" checkbox -- see photo-library.php) with
// the same reorder/remove-from-contract/preview affordances a section's
// photo grid has. Prints right before Section 1 in the PDF.

use Src\Controller\InspectionLibraryController;

/** @var \Illuminate\Support\Collection $links InspectionPictureContract rows, picture eager-loaded */
/** @var string $assetBase */
/** @var string $encodedInspectionId */
/** @var bool $isLocked */
?>

<div id="section-content-inner" data-section-id="contracts">
    <div class="p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between mb-1 flex-wrap gap-2">
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Contracts</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Prints right before Section 1 in the PDF. Assign photos as contract documents from the Photo Library.</p>
            </div>
            <?php if (!$isLocked): ?>
                <button type="button" data-action="switch-to-library" data-tab="library-photos"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-primary-600 hover:text-primary-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Add from Photo Library
                </button>
            <?php endif; ?>
        </div>

        <div id="contracts-grid" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php foreach ($links as $link): ?>
                <?= InspectionLibraryController::renderContractPictureCard($link, $isLocked) ?>
            <?php endforeach; ?>
        </div>
        <p id="contracts-empty" class="text-xs text-gray-400 mt-3 <?= $links->isNotEmpty() ? 'hidden' : '' ?>">No contract documents assigned yet.</p>
    </div>
</div>
