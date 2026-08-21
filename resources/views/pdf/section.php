<?php
// /resources/views/pdf/section.php
//
// Renders one section's worth of PDF content: title header, the question
// checklist (split into the main checklist and any "Condition / Limitation"
// questions, matching legacy's PDFBody split), the section's free-text
// comment if any, then that section's photo grid with captions. Wrapped by
// the caller in a page-break-after block, so this file only emits content.

use App\Models\InspectionQuestion;
use App\Models\Question;

/** @var \App\Models\Inspection $inspection */
/** @var \App\Models\Section $section */
/** @var \Illuminate\Support\Collection $questions bank Questions for this section, with options/fields eager-loaded */
/** @var \Illuminate\Support\Collection $answers InspectionQuestion rows keyed by question_id */
/** @var \Illuminate\Support\Collection $selectedOptionsByQuestion grouped by question_id */
/** @var \Illuminate\Support\Collection $fieldResponsesByQuestion grouped by question_id */
/** @var \App\Models\InspectionSectionComment|null $sectionComment */
/** @var \Illuminate\Support\Collection $pictureLinks InspectionPictureSection rows for this section, picture eager-loaded */
/** @var string $fullAddress */

$statusBadge = function (int $tasks): string {
    return match ($tasks) {
        InspectionQuestion::TASK_INSPECTED => '<span class="pdf-status-badge pdf-status-inspected">Inspected</span>',
        InspectionQuestion::TASK_NOT_INSPECTED => '<span class="pdf-status-badge pdf-status-not-inspected">Not Inspected</span>',
        InspectionQuestion::TASK_NOT_APPLICABLE => '<span class="pdf-status-badge pdf-status-na">N/A</span>',
        default => '',
    };
};

$renderQuestion = function (Question $question) use ($answers, $selectedOptionsByQuestion, $fieldResponsesByQuestion, $statusBadge): string {
    $answer = $answers[$question->id] ?? null;
    $tasks = (int)($answer->tasks ?? InspectionQuestion::TASK_BLANK);
    $notes = trim((string)($answer->notes ?? ''));

    $selectedOptionIds = ($selectedOptionsByQuestion[$question->id] ?? collect())
        ->pluck('question_option_id')->map(fn($id) => (int)$id)->all();

    $fieldResponseMap = [];
    foreach (($fieldResponsesByQuestion[$question->id] ?? collect()) as $row) {
        if (trim((string)$row->response_text) !== '') {
            $fieldResponseMap[(int)$row->question_field_id] = $row->response_text;
        }
    }

    // Every option is always listed (not just the checked ones) so the PDF
    // reads as a real checklist -- a checked box glyph for what was
    // selected, an empty box glyph for what wasn't. A CSS-drawn bordered
    // box (fixed width/height on a span) proved unreliable in mPDF -- it
    // either collapsed to nothing when empty, or distorted around whatever
    // filler content was used to stop that. Real ballot-box glyphs (☐/☑)
    // sidestep box-sizing entirely; like the checkmark, Quicksand doesn't
    // have them so they borrow mPDF's default sans fallback face.
    $optionsHtml = '';
    if ($question->options->isNotEmpty()) {
        $optionsHtml = '<div class="pdf-options">' . $question->options->map(function ($o) use ($selectedOptionIds) {
            $isChecked = in_array((int)$o->id, $selectedOptionIds, true);
            $text = htmlspecialchars($o->option_text);
            // Checked options stand out (bold, green); unchecked ones fade
            // back (light gray, box included) so the two states are
            // distinguishable at a glance, not just by which glyph it is.
            $glyph = $isChecked
                ? '<span style="font-family:sans-serif;color:#15803d;">&#9745;</span>'
                : '<span style="font-family:sans-serif;color:#9ca3af;">&#9744;</span>';
            return $isChecked
                ? '<span class="opt"><strong style="color:#15803d;">' . $glyph . '&nbsp;' . $text . '</strong></span>'
                : '<span class="opt" style="color:#9ca3af;">' . $glyph . '&nbsp;' . $text . '</span>';
        })->implode('&nbsp;&nbsp;&nbsp;&nbsp;') . '</div>';
    }

    $fieldsHtml = '';
    $filledFields = $question->fields->filter(fn($f) => isset($fieldResponseMap[$f->id]));
    if ($filledFields->isNotEmpty()) {
        $fieldsHtml = '<div class="pdf-fields">' . $filledFields->map(function ($f) use ($fieldResponseMap) {
            $prefix = $f->prefix ? htmlspecialchars($f->prefix) . ' ' : '';
            $suffix = $f->suffix ? ' ' . htmlspecialchars($f->suffix) : '';
            return $prefix . '<strong>' . htmlspecialchars($fieldResponseMap[$f->id]) . '</strong>' . $suffix;
        })->implode(' &nbsp;&nbsp; ') . '</div>';
    }

    $notesHtml = $notes !== '' ? '<div class="pdf-notes">' . nl2br(htmlspecialchars($notes)) . '</div>' : '';

    $badge = $statusBadge($tasks);
    $isPerform = (int)$question->answer_mode === Question::MODE_PERFORM;
    // Quicksand has no glyph for U+25C6/U+25A0 -- like the ballot-box
    // checkmarks above, force mPDF's Unicode-capable sans-serif fallback
    // face explicitly, or these render as a "missing glyph" tofu box.
    $modeGlyph = '<span style="font-family:sans-serif;">' . ($isPerform ? '&#9830;&#9632;' : '&#9830;') . '</span>';
    $modeBadge = '<span class="pdf-mode-badge' . ($isPerform ? ' pdf-mode-perform' : '') . '">'
        . $modeGlyph . '&nbsp;' . ($isPerform ? 'Perform Tasks' : 'Observe &amp; Report') . '</span>';
    return '<div class="pdf-question">'
        . '<span class="pdf-question-title">' . htmlspecialchars($question->question_title) . '</span>'
        . '&nbsp;' . $modeBadge
        . ($badge !== '' ? '&nbsp;' . $badge : '')
        . $optionsHtml . $fieldsHtml . $notesHtml
        . '</div>';
};

$mainQuestions = $questions->filter(fn($q) => !$q->is_condition);
$conditionQuestions = $questions->filter(fn($q) => $q->is_condition);
?>

<div class="pdf-section-header">
    <div class="pdf-section-title"><?= htmlspecialchars($section->name) ?></div>
    <div class="pdf-section-address"><?= htmlspecialchars($fullAddress) ?></div>
</div>

<?php if ($mainQuestions->isEmpty() && $conditionQuestions->isEmpty()): ?>
    <p class="pdf-empty-note">No questions defined for this section.</p>
<?php else: ?>
    <?php foreach ($mainQuestions as $question): ?>
        <?= $renderQuestion($question) ?>
    <?php endforeach; ?>

    <?php if ($conditionQuestions->isNotEmpty()): ?>
        <div class="pdf-conditions-heading">Conditions &amp; Limitations</div>
        <?php foreach ($conditionQuestions as $question): ?>
            <?= $renderQuestion($question) ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/section-footer.php'; ?>

<?php if ($sectionComment && trim((string)$sectionComment->comments) !== ''): ?>
    <div class="pdf-section-comment">
        <div class="label">Inspector Comments</div>
        <?= nl2br(htmlspecialchars($sectionComment->comments)) ?>
    </div>
<?php endif; ?>

<?php if ($pictureLinks->isNotEmpty()): ?>
    <div class="pdf-photos-heading"><?= htmlspecialchars($section->name) ?> &mdash; Photographs</div>
    <table style="width:100%; border-collapse:collapse;">
        <?php foreach ($pictureLinks->chunk(2) as $pair): ?>
            <tr>
                <?php foreach ($pair as $link): ?>
                    <?php
                    $picPath = realpath(__DIR__ . '/../../../public/images/uploads/inspections/' . $inspection->id . '/' . $link->picture->filename);
                    if (!$picPath) continue;
                    ?>
                    <td class="pdf-photo-cell">
                        <img src="<?= $picPath ?>" alt="">
                        <?php if (trim((string)$link->description) !== ''): ?>
                            <div class="pdf-photo-caption"><?= htmlspecialchars($link->description) ?></div>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <?php if ($pair->count() === 1): ?><td class="pdf-photo-cell"></td><?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
