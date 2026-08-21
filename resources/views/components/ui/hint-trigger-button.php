<?php
// /resources/views/components/ui/hint-trigger-button.php
//
// Opens the shared "hints" picker (resources/js/modals/note-hints-modal.js)
// for one notes-type field. The type + section determine which pool of
// saved phrases it loads -- see NoteHintsController.

/**
 * @var string $hintType 'inspection_summary' | 'question_notes' | 'section_comment'
 * @var string|null $hintSectionId encoded Section id -- omit/null for 'inspection_summary'
 * @var bool|null $hintCompact icon-only, for tight spaces (e.g. one per question card)
 */

$hintCompact = $hintCompact ?? false;
?>
<button type="button" data-action="open-note-hints" data-hint-type="<?= htmlspecialchars($hintType) ?>"
    <?= !empty($hintSectionId) ? 'data-section-id="' . htmlspecialchars($hintSectionId) . '"' : '' ?>
    title="Hints" class="inline-flex items-center gap-1 <?= $hintCompact ? 'p-1 rounded-md bg-white/90 dark:bg-gray-900/90 border border-gray-200 dark:border-gray-700 shadow-sm' : '' ?> text-[10px] font-bold text-secondary-600 dark:text-secondary-400 hover:text-secondary-800 dark:hover:text-secondary-300 transition-colors">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
    </svg>
    <?php if (!$hintCompact): ?>Hints<?php endif; ?>
</button>
<?php $hintCompact = false; ?>
