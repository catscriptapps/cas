<?php
// /resources/views/components/questions/question-row.php

use App\Utils\IdEncoder;
use App\Models\Question;

/** @var Question $question */

$encodedId = IdEncoder::encode((int)$question->id);
$encodedSectionId = IdEncoder::encode((int)$question->section_id);
$title = htmlspecialchars($question->question_title);

$modeLabel = (int)$question->answer_mode === Question::MODE_PERFORM ? 'Perform Tasks' : 'Observe & Report';
$modeBadge = (int)$question->answer_mode === Question::MODE_PERFORM
    ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30">Perform Tasks</span>'
    : '<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Observe &amp; Report</span>';

$conditionBadge = $question->is_condition
    ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-secondary-50 dark:bg-secondary-900/20 text-secondary-600 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-800/30">Condition / Limitation</span>'
    : '';

$accentBorder = (int)$question->answer_mode === Question::MODE_PERFORM ? 'border-l-4 border-l-primary-400 dark:border-l-primary-600' : 'border-l-4 border-l-secondary-300 dark:border-l-secondary-700';

$optionsList = $question->options ?? collect();
$fieldsList = $question->fields ?? collect();

$optionsJson = htmlspecialchars(json_encode($optionsList->pluck('option_text')->values()), ENT_QUOTES);
$fieldsJson = htmlspecialchars(json_encode($fieldsList->map(fn($f) => ['prefix' => $f->prefix, 'suffix' => $f->suffix])->values()), ENT_QUOTES);
?>

<div class="question-card group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 <?= $accentBorder ?> p-4 sm:p-5 shadow-sm hover:shadow-md transition-all"
    data-question-id="<?= $encodedId ?>">
    <div class="flex items-start gap-3">
        <button type="button" data-action="reorder-question" data-id="<?= $encodedId ?>" title="Click to move this question, then click another to place it there"
            class="reorder-question-btn shrink-0 mt-0.5 p-1.5 rounded-md text-gray-400 hover:text-secondary-600 hover:bg-secondary-50 dark:hover:bg-secondary-900/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
            </svg>
        </button>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <h4 class="font-bold text-sm text-secondary-900 dark:text-white leading-snug"><?= $title ?></h4>
                <?= $conditionBadge ?>
                <?= $modeBadge ?>
            </div>

            <?php if ($optionsList->isNotEmpty()): ?>
                <div class="flex flex-wrap gap-1.5 mb-1.5">
                    <?php foreach ($optionsList as $option): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700">
                            <?= htmlspecialchars($option->option_text) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($fieldsList->isNotEmpty()): ?>
                <div class="flex flex-wrap gap-1.5">
                    <?php foreach ($fieldsList as $field): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-dashed dark:border-gray-700 border-dashed">
                            <?= htmlspecialchars($field->prefix ?? '') ?> ___ <?= htmlspecialchars($field->suffix ?? '') ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" data-action="edit-question"
                data-encoded-id="<?= $encodedId ?>"
                data-section-id="<?= $encodedSectionId ?>"
                data-question-title="<?= htmlspecialchars($question->question_title, ENT_QUOTES) ?>"
                data-answer-mode="<?= (int)$question->answer_mode ?>"
                data-is-condition="<?= $question->is_condition ? '1' : '0' ?>"
                data-options="<?= $optionsJson ?>"
                data-fields="<?= $fieldsJson ?>"
                class="edit-question-btn p-1.5 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button type="button" data-action="delete-question" data-id="<?= $encodedId ?>"
                class="delete-question-btn p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>
</div>
