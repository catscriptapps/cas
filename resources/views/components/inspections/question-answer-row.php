<?php
// /resources/views/components/inspections/question-answer-row.php

use App\Models\Question;
use App\Models\InspectionQuestion;
use App\Utils\IdEncoder;

/** @var Question $question */
/** @var \Illuminate\Support\Collection $answers keyed by question_id */
/** @var \Illuminate\Support\Collection $selectedOptionsByQuestion grouped by question_id */
/** @var \Illuminate\Support\Collection $fieldResponsesByQuestion grouped by question_id */
/** @var bool $isLocked */

$isLocked = $isLocked ?? false;
$encodedQuestionId = IdEncoder::encode((int)$question->id);
$answer = $answers[$question->id] ?? null;
$tasks = $answer->tasks ?? InspectionQuestion::TASK_BLANK;
$notes = $answer->notes ?? '';

$selectedOptionIds = array_map(
    fn($row) => (int)$row->question_option_id,
    ($selectedOptionsByQuestion[$question->id] ?? collect())->all()
);

$fieldResponseMap = [];
foreach (($fieldResponsesByQuestion[$question->id] ?? collect()) as $row) {
    $fieldResponseMap[(int)$row->question_field_id] = $row->response_text;
}

// The diamond/square glyphs are legacy's actual Tasks-column marks (a lone
// diamond for Observe & Report, diamond+square together for Perform Tasks)
// -- kept alongside the text label here rather than replacing it, since the
// glyphs alone are unreadable at a glance without legacy's printed legend.
$modeBadge = (int)$question->answer_mode === Question::MODE_PERFORM
    ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30"><span aria-hidden="true">&#9830;&#9632;</span>Perform Tasks</span>'
    : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700"><span aria-hidden="true">&#9830;</span>Observe &amp; Report</span>';

$conditionBadge = $question->is_condition
    ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-secondary-50 dark:bg-secondary-900/20 text-secondary-600 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-800/30">Condition / Limitation</span>'
    : '';

$accentBorder = (int)$question->answer_mode === Question::MODE_PERFORM ? 'border-l-4 border-l-primary-400 dark:border-l-primary-600' : 'border-l-4 border-l-secondary-300 dark:border-l-secondary-700';

$taskOptions = [
    InspectionQuestion::TASK_BLANK => '— Select —',
    InspectionQuestion::TASK_NOT_APPLICABLE => 'N/A',
    InspectionQuestion::TASK_INSPECTED => '✓ Inspected',
    InspectionQuestion::TASK_NOT_INSPECTED => '✗ Not Inspected',
];

// Keyed by option value so the select's background/border can swap to match
// whichever option is picked -- both on initial render (server-side, below)
// and instantly on change (client-side, via each <option>'s data-classes;
// see answer-autosave.js) without waiting for a save round-trip.
$taskSelectClassMap = [
    InspectionQuestion::TASK_BLANK => 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300',
    InspectionQuestion::TASK_NOT_APPLICABLE => 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-500',
    InspectionQuestion::TASK_INSPECTED => 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-800 text-green-700 dark:text-green-400',
    InspectionQuestion::TASK_NOT_INSPECTED => 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800 text-red-700 dark:text-red-400',
];
$taskSelectBaseClasses = 'w-full rounded-lg border text-xs font-bold py-2 px-2.5 focus:ring-2 focus:ring-primary-400 transition-colors';
$taskSelectClasses = $taskSelectClassMap[(int)$tasks] ?? $taskSelectClassMap[InspectionQuestion::TASK_BLANK];
?>

<div class="inspection-question-card bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 <?= $accentBorder ?> p-4 sm:p-5 shadow-sm"
    data-question-id="<?= $encodedQuestionId ?>">
    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <h4 class="font-bold text-sm text-secondary-900 dark:text-white leading-snug"><?= htmlspecialchars($question->question_title) ?></h4>
                <?= $conditionBadge ?>
                <?= $modeBadge ?>
            </div>

            <?php if ($question->options->isNotEmpty()): ?>
                <div class="flex flex-wrap gap-3 mb-3">
                    <?php foreach ($question->options as $option): ?>
                        <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" data-role="option-checkbox" value="<?= IdEncoder::encode((int)$option->id) ?>"
                                <?= in_array((int)$option->id, $selectedOptionIds, true) ? 'checked' : '' ?>
                                <?= $isLocked ? 'disabled' : '' ?>
                                class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                            <?= htmlspecialchars($option->option_text) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($question->fields->isNotEmpty()): ?>
                <div class="flex flex-wrap gap-3 mb-3">
                    <?php foreach ($question->fields as $field): ?>
                        <div class="inline-flex items-center gap-1.5 text-xs">
                            <?php if ($field->prefix): ?><span class="text-gray-500 dark:text-gray-400 font-semibold"><?= htmlspecialchars($field->prefix) ?></span><?php endif; ?>
                            <input type="text" data-role="field-input" data-field-id="<?= IdEncoder::encode((int)$field->id) ?>"
                                value="<?= htmlspecialchars($fieldResponseMap[$field->id] ?? '') ?>" <?= $isLocked ? 'readonly' : '' ?>
                                class="w-24 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-xs py-1 px-2 focus:border-primary-400 focus:ring-primary-400">
                            <?php if ($field->suffix): ?><span class="text-gray-500 dark:text-gray-400 font-semibold"><?= htmlspecialchars($field->suffix) ?></span><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="relative">
                <textarea data-role="notes-textarea" placeholder="Notes..." rows="2" <?= $isLocked ? 'readonly' : '' ?>
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 text-gray-900 dark:text-white text-xs py-2 <?= $isLocked ? 'px-3' : 'pl-3 pr-9' ?> placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 resize-y"><?= htmlspecialchars($notes) ?></textarea>
                <?php if (!$isLocked): $hintType = 'question_notes';
                    $hintSectionId = IdEncoder::encode((int)$question->section_id);
                    $hintCompact = true;
                    ?>
                    <div class="absolute top-1.5 right-1.5">
                        <?php include __DIR__ . '/../ui/hint-trigger-button.php'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="shrink-0 sm:w-44">
            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Status</label>
            <select data-role="tasks-select" data-base-classes="<?= $taskSelectBaseClasses ?>" <?= $isLocked ? 'disabled' : '' ?> class="<?= $taskSelectBaseClasses ?> <?= $taskSelectClasses ?>">
                <?php foreach ($taskOptions as $value => $label): ?>
                    <option value="<?= $value ?>" data-classes="<?= htmlspecialchars($taskSelectClassMap[$value]) ?>" <?= (int)$tasks === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <span data-role="save-indicator" class="hidden mt-1.5 text-[10px] font-bold text-green-600 dark:text-green-400">Saved</span>
        </div>
    </div>
</div>
