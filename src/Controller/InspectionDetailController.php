<?php
// /src/Controller/InspectionDetailController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Inspection;
use App\Models\Section;
use App\Models\Question;
use App\Models\InspectionQuestion;
use App\Models\InspectionQuestionOption;
use App\Models\InspectionQuestionField;
use App\Models\InspectionSectionComment;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;

/**
 * Manages the inspection detail/fill-in page: section tabs, per-question
 * answer capture (Tasks status, checkbox options, fill-in fields, notes),
 * per-section comments, and per-section photos. Everything here is scoped
 * to a single Inspection resolved by the caller (see inspections/detail.php
 * and the server/api/inspection-*.php endpoints), never to "the current
 * company" directly, since an Inspector's inspections all belong to their
 * company but not every company inspection belongs to them.
 */
class InspectionDetailController
{
    use RecentActivityLogger;

    /**
     * A finalized report (hasReport() -- date_expires is set, regardless of
     * whether that window has since expired) is locked against every kind
     * of edit until explicitly reopened, so nothing can drift out of sync
     * with the PDF that's already been handed to a client. Every mutating
     * method below throws this at the top of its try block, reusing that
     * method's own catch to shape the response the same way any other
     * failure would.
     */
    private const LOCKED_MESSAGE = 'This report has been finalized and is locked. Reopen it for editing to make changes.';

    public static function sectionsForInspection(Inspection $inspection)
    {
        return Section::where('company_id', $inspection->company_id)
            ->where('status_id', 1)
            ->orderBy('pos_index')
            ->get();
    }

    public static function renderTab(Section $section, bool $isActive): string
    {
        $encodedId = IdEncoder::encode((int)$section->id);
        $icon = \Src\Config\SectionIcons::svg($section->icon, 'w-4 h-4');
        $name = htmlspecialchars($section->name);

        // Same icon-tinted resting color as the Questions page's own tabs
        // (SectionsController::renderTab()) -- shared via SectionIcons so
        // a section's color stays consistent wherever it's browsed from,
        // including the guest report view, which reuses this markup as-is.
        $inactiveClasses = \Src\Config\SectionIcons::tabColor($section->icon);
        $activeClasses = \Src\Config\SectionIcons::ACTIVE_TAB_CLASSES;
        $currentClasses = $isActive ? $activeClasses : $inactiveClasses;

        return <<<HTML
        <button type="button" data-action="select-inspection-section" data-id="{$encodedId}" data-name="{$name}"
            data-active-classes="{$activeClasses}" data-inactive-classes="{$inactiveClasses}"
            class="inspection-section-tab-btn inline-flex items-center gap-2 px-2.5 py-2.5 rounded-xl border font-bold text-xs uppercase tracking-wider transition-all shrink-0 {$currentClasses}">
            <span class="hidden">{$icon}</span>
            <span>{$name}</span>
        </button>
        HTML;
    }

    /**
     * The three non-section tabs living alongside the Section tabs: Photo
     * Library / Video Library (upload lives here now, not per-section) and
     * Contracts (assigned from the Photo Library, printed right before
     * Section 1 in the PDF). Their ids are plain literals, not
     * IdEncoder-encoded, since they don't map to a database row -- see
     * section-tabs.js, which special-cases these three against a real
     * encoded section id to decide which endpoint to hit.
     */
    private const VIRTUAL_TABS = [
        'library-photos' => ['label' => 'Photo Library', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'library-videos' => ['label' => 'Video Library', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
        'contracts' => ['label' => 'Contracts', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ];

    /**
     * $count renders a live badge next to the label (Photo/Video Library
     * only -- Contracts has no count) -- kept in sync client-side after
     * every library upload/delete without a tab reload, see
     * photo-library.js/video-library.js's updateTabCount().
     */
    public static function renderVirtualTab(string $key, ?int $count = null): string
    {
        $meta = self::VIRTUAL_TABS[$key];
        $name = htmlspecialchars($meta['label']);
        $inactiveClasses = 'bg-secondary-50 dark:bg-secondary-900/20 text-secondary-700 dark:text-secondary-400 border-secondary-200 dark:border-secondary-800/40';
        $activeClasses = \Src\Config\SectionIcons::ACTIVE_TAB_CLASSES;

        $countBadge = $count !== null
            ? '<span data-role="tab-count" data-count-key="' . $key . '" class="inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-black/10 dark:bg-white/10 text-[9px] font-black">' . $count . '</span>'
            : '';

        return <<<HTML
        <button type="button" data-action="select-inspection-section" data-id="{$key}" data-name="{$name}"
            data-active-classes="{$activeClasses}" data-inactive-classes="{$inactiveClasses}"
            class="inspection-section-tab-btn inline-flex items-center gap-1.5 px-2.5 py-2.5 rounded-xl border font-bold text-xs uppercase tracking-wider transition-all shrink-0 {$inactiveClasses}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{$meta['icon']}" /></svg>
            <span>{$name}</span>
            {$countBadge}
        </button>
        HTML;
    }

    /**
     * Initial page load: tab bar plus the first section's content prerendered.
     * $includeLibraryTabs is false for the public guest report view (see
     * pages/report/detail.php) -- Photo/Video Library and Contracts are
     * staff organization tools (upload, delete, checkbox-assign), not
     * something a client needs to browse, and the guest page's own JS/API
     * (report-page.js / api/report-detail.php) has no route for them anyway.
     */
    public function index(Inspection $inspection, bool $includeLibraryTabs = true): void
    {
        $sections = self::sectionsForInspection($inspection);
        $activeEncodedId = $sections->isNotEmpty() ? IdEncoder::encode((int)$sections->first()->id) : null;

        $tabsHtml = '';
        if ($includeLibraryTabs) {
            $counts = InspectionLibraryController::libraryCounts($inspection);
            foreach (array_keys(self::VIRTUAL_TABS) as $key) {
                $tabsHtml .= self::renderVirtualTab($key, $counts[$key] ?? null);
            }
        }
        foreach ($sections as $section) {
            $tabsHtml .= self::renderTab($section, IdEncoder::encode((int)$section->id) === $activeEncodedId);
        }

        $GLOBALS['sectionTabsHtml'] = $tabsHtml;
        $GLOBALS['sectionsCount'] = $sections->count();
        $GLOBALS['activeSectionEncodedId'] = $activeEncodedId;
        $GLOBALS['activeSectionContentHtml'] = $sections->isNotEmpty()
            ? self::renderSectionContent($inspection, $sections->first())
            : '';
    }

    /**
     * AJAX tab-switch: returns the HTML for one section's content.
     */
    public function sectionContent(Inspection $inspection, string $encodedSectionId): void
    {
        $sectionId = IdEncoder::decode($encodedSectionId);
        $section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $inspection->company_id)->first() : null;

        header('Content-Type: application/json');

        if (!$section) {
            echo json_encode(['success' => false, 'messages' => ['Section not found.']]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'html' => self::renderSectionContent($inspection, $section),
            'sectionName' => $section->name,
        ]);
        exit;
    }

    /**
     * Renders one section's question list (each joined to its saved answer,
     * if any), the section comment box, and the section's photo grid.
     */
    public static function renderSectionContent(Inspection $inspection, Section $section): string
    {
        $questions = Question::with(['options', 'fields'])
            ->where('company_id', $inspection->company_id)
            ->where('section_id', $section->id)
            ->where('status_id', 1)
            ->orderBy('pos_index')
            ->get();

        $questionIds = $questions->pluck('id');

        $answers = InspectionQuestion::where('inspection_id', $inspection->id)
            ->whereIn('question_id', $questionIds)->get()->keyBy('question_id');
        $selectedOptionsByQuestion = InspectionQuestionOption::where('inspection_id', $inspection->id)
            ->whereIn('question_id', $questionIds)->get()->groupBy('question_id');
        $fieldResponsesByQuestion = InspectionQuestionField::where('inspection_id', $inspection->id)
            ->whereIn('question_id', $questionIds)->get()->groupBy('question_id');

        $sectionComment = InspectionSectionComment::where('inspection_id', $inspection->id)
            ->where('section_id', $section->id)->first();

        // Section grids are now driven by the junction tables (a photo/video
        // is assigned here from the Photo/Video Library, not uploaded
        // directly) -- see InspectionLibraryController.
        $pictureLinks = InspectionLibraryController::picturesForSection($inspection, $section);
        $videoLinks = InspectionLibraryController::videosForSection($inspection, $section);

        $GLOBALS['assetBase'] = getAssetBase();
        $isLocked = $inspection->hasReport();

        ob_start();
        try {
            $assetBase = getAssetBase();
            $encodedInspectionId = IdEncoder::encode((int)$inspection->id);
            $encodedSectionId = IdEncoder::encode((int)$section->id);
            include __DIR__ . '/../../resources/views/components/inspections/section-content.php';
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<div class='p-4 text-red-500 text-sm'>Render Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        return ob_get_clean();
    }

    /**
     * Saves one question's full answer bundle in a single request: Tasks
     * status, notes, the full set of currently-checked options, and the
     * full set of filled-in field responses. Options/fields are fully
     * replaced each save (delete-then-reinsert), same simple approach used
     * for the question-bank template itself.
     */
    public function saveAnswer(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $questionId = IdEncoder::decode((string)($data['question_id'] ?? ''));
            $question = $questionId ? Question::where('id', $questionId)->where('company_id', $inspection->company_id)->first() : null;
            if (!$question) throw new \Exception('Question not found.');

            $tasks = (int)($data['tasks'] ?? 0);
            if ($tasks < 0 || $tasks > 3) $tasks = 0;
            $notes = trim((string)($data['notes'] ?? ''));

            InspectionQuestion::updateOrCreate(
                ['inspection_id' => $inspection->id, 'question_id' => $question->id],
                ['section_id' => $question->section_id, 'tasks' => $tasks, 'notes' => $notes !== '' ? $notes : null]
            );

            InspectionQuestionOption::where('inspection_id', $inspection->id)->where('question_id', $question->id)->delete();
            $optionIds = is_array($data['option_ids'] ?? null) ? $data['option_ids'] : [];
            foreach ($optionIds as $encodedOptionId) {
                $optionId = IdEncoder::decode((string)$encodedOptionId);
                if (!$optionId) continue;
                InspectionQuestionOption::create([
                    'inspection_id' => $inspection->id,
                    'question_id' => $question->id,
                    'question_option_id' => $optionId,
                ]);
            }

            InspectionQuestionField::where('inspection_id', $inspection->id)->where('question_id', $question->id)->delete();
            $fields = is_array($data['fields'] ?? null) ? $data['fields'] : [];
            foreach ($fields as $encodedFieldId => $responseText) {
                $fieldId = IdEncoder::decode((string)$encodedFieldId);
                if (!$fieldId) continue;
                $text = trim((string)$responseText);
                if ($text === '') continue;
                InspectionQuestionField::create([
                    'inspection_id' => $inspection->id,
                    'question_id' => $question->id,
                    'question_field_id' => $fieldId,
                    'response_text' => $text,
                ]);
            }

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function saveSectionComment(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $sectionId = IdEncoder::decode((string)($data['section_id'] ?? ''));
            $section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $inspection->company_id)->first() : null;
            if (!$section) throw new \Exception('Section not found.');

            $comments = trim((string)($data['comments'] ?? ''));

            InspectionSectionComment::updateOrCreate(
                ['inspection_id' => $inspection->id, 'section_id' => $section->id],
                ['comments' => $comments !== '' ? $comments : null]
            );

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * The inspector's initials, editable from any section's footer legend
     * (see section-footer.php) since it's one field on the inspection
     * itself, not scoped per section -- matches legacy's hit_inspections.initials.
     */
    public function saveInitials(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $initials = trim((string)($data['initials'] ?? ''));
            $inspection->initials = $initials !== '' ? substr($initials, 0, 10) : null;
            $inspection->save();

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function saveSummary(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $summary = trim((string)($data['inspection_summary'] ?? ''));
            $inspection->inspection_summary = $summary !== '' ? $summary : null;
            $inspection->save();

            return ['success' => true, 'messages' => ['Summary saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
