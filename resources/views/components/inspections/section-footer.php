<?php
// /resources/views/components/inspections/section-footer.php
//
// The legend + sign-off bar legacy prints at the bottom of every section
// (pages/detail/inspections/footer.php + pdf/pdf_body.php's footer()),
// ported here for the fill-in screen and reused (as static print output)
// for the PDF's equivalent (see pdf/section-footer.php). Explains the two
// per-question glyphs (diamond = Observe & Report, square = Perform Tasks,
// combined for a "Perform Tasks" question) alongside the status glyphs
// (checkmark/x/n over a), and carries the one inspector-initials field that
// applies to the whole inspection, editable from any section.

use App\Models\Inspection;

/** @var Inspection $inspection */
/** @var bool $isLocked */

$isLocked = $isLocked ?? false;
$initialsValue = htmlspecialchars($inspection->initials ?? '');
$dateLabel = $inspection->date_created ? $inspection->date_created->format('F j, Y') : '';
?>
<div class="mt-6 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
    <div class="px-4 sm:px-5 py-2.5 bg-secondary-900 dark:bg-secondary-950">
        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-white/70">Legend &amp; Sign-Off</h3>
    </div>
    <div class="p-4 sm:p-5 grid sm:grid-cols-2 gap-x-8 gap-y-4">
        <div class="space-y-2">
            <div class="flex items-center gap-2.5 text-xs">
                <span class="w-6 text-center text-base leading-none text-secondary-700 dark:text-secondary-300">&#10004;</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">Inspected</span>
            </div>
            <div class="flex items-center gap-2.5 text-xs">
                <span class="w-6 text-center text-base leading-none text-secondary-700 dark:text-secondary-300">x</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">Not Inspected</span>
            </div>
            <div class="flex items-center gap-2.5 text-xs">
                <span class="w-6 text-center text-base leading-none text-secondary-700 dark:text-secondary-300">n&frasl;a</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">Not Applicable</span>
            </div>
            <?php if ($dateLabel): ?>
                <div class="flex items-center gap-2.5 text-xs pt-1 text-gray-400 dark:text-gray-500">
                    <span class="font-semibold">Date:</span>
                    <span><?= htmlspecialchars($dateLabel) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-2">
            <div class="flex items-center gap-2.5 text-xs">
                <span class="w-6 text-center text-base leading-none text-secondary-700 dark:text-secondary-300">&#9830;</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">Observe and Report</span>
            </div>
            <div class="flex items-center gap-2.5 text-xs">
                <span class="w-6 text-center text-sm leading-none text-secondary-700 dark:text-secondary-300">&#9632;</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">Perform Tasks</span>
            </div>

            <div class="flex items-center gap-2.5 text-xs pt-1">
                <label for="inspection-initials-input" class="font-semibold text-gray-700 dark:text-gray-300 shrink-0">Inspector Initials:</label>
                <input type="text" id="inspection-initials-input" maxlength="10" <?= $isLocked ? 'readonly' : '' ?>
                    value="<?= $initialsValue ?>" placeholder="&mdash;"
                    class="w-20 rounded-md border-0 border-b-2 border-dotted border-primary-400 bg-transparent text-primary-700 dark:text-primary-400 font-bold text-center focus:ring-0 focus:border-primary-500 placeholder:text-gray-300">
                <span id="inspection-initials-save-indicator" class="hidden text-[10px] font-bold text-green-600 dark:text-green-400">Saved</span>
            </div>
        </div>
    </div>
    <div class="px-4 sm:px-5 py-2 border-t border-gray-100 dark:border-gray-800 text-center text-[10px] text-gray-400 dark:text-gray-500">
        &copy; <?= date('Y') ?> Home Comfort Reports
    </div>
</div>
