<?php
// /src/Controller/SectionsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Section;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionField;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;
use Src\Config\SectionIcons;

/**
 * Manages the Section tabs a Company Admin organizes their question bank
 * into. Always scoped to the signed-in Company Admin's own company.
 */
class SectionsController
{
    use RecentActivityLogger;

    /**
     * Fetch the current company's sections, ordered for tab display.
     * @return \Illuminate\Support\Collection<int, Section>
     */
    public static function forCurrentCompany()
    {
        $currentUser = AuthService::currentUser();
        $companyId = $currentUser->company_id ?? 0;

        return Section::where('company_id', $companyId)
            ->where('status_id', 1)
            ->orderBy('pos_index')
            ->get();
    }

    /**
     * Render a single section as a clickable tab. Resting-state color comes
     * from SectionIcons::tabColor() -- shared with InspectionDetailController's
     * own renderTab(), so a section's tint is consistent everywhere it's
     * browsed from (Questions, an inspection's fill-in tabs, the guest
     * report view).
     */
    public static function renderTab(Section $section, bool $isActive = false): string
    {
        $encodedId = IdEncoder::encode((int)$section->id);
        $icon = SectionIcons::svg($section->icon, 'w-4 h-4');
        $name = htmlspecialchars($section->name);

        $inactiveClasses = SectionIcons::tabColor($section->icon);
        $activeClasses = SectionIcons::ACTIVE_TAB_CLASSES;
        $currentClasses = $isActive ? $activeClasses : $inactiveClasses;

        return <<<HTML
        <div class="section-tab group relative inline-flex items-center shrink-0" data-section-id="{$encodedId}">
            <button type="button" data-action="select-section" data-id="{$encodedId}" data-name="{$name}" data-icon="{$section->icon}"
                data-active-classes="{$activeClasses}" data-inactive-classes="{$inactiveClasses}"
                class="section-tab-btn inline-flex items-center gap-2 pl-2.5 pr-7 py-2.5 rounded-xl border font-bold text-xs uppercase tracking-wider transition-all {$currentClasses}">
                <span class="hidden">{$icon}</span>
                <span>{$name}</span>
            </button>
            <button type="button" data-action="reorder-section" data-id="{$encodedId}" title="Click to move this section, then click another to place it there"
                class="reorder-section-btn absolute right-1.5 top-1/2 -translate-y-1/2 p-1 rounded-md text-current opacity-60 hover:opacity-100 transition-opacity">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </button>
        </div>
        HTML;
    }

    /**
     * Prepare data for the initial page load: the tab bar HTML and which
     * section is active (the first by pos_index, if any).
     */
    public function index(): void
    {
        $sections = self::forCurrentCompany();

        $activeEncodedId = $sections->isNotEmpty() ? IdEncoder::encode((int)$sections->first()->id) : null;

        $html = '';
        foreach ($sections as $section) {
            $html .= self::renderTab($section, IdEncoder::encode((int)$section->id) === $activeEncodedId);
        }

        $GLOBALS['sectionTabs'] = $html;
        $GLOBALS['sectionsCount'] = $sections->count();
        $GLOBALS['activeSectionEncodedId'] = $activeEncodedId;
    }

    /**
     * Create or update a section. Returns the freshly rendered tab so the
     * caller can patch the DOM directly instead of reloading the tab bar.
     */
    public function save(array $data): array
    {
        try {
            $currentUser = AuthService::currentUser();
            $companyId = $currentUser->company_id ?? 0;
            if (!$companyId) throw new \Exception("No company associated with this account.");

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);

            $name = trim($data['name'] ?? '');
            if ($name === '') throw new \Exception("Section name is required.");

            $sectionId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $companyId)->first() : new Section();
            if (!$section) throw new \Exception("Section not found.");

            $section->company_id = $companyId;
            $section->name = $name;
            $section->icon = $data['icon'] ?? 'general';

            if ($isNew) {
                $section->pos_index = Section::where('company_id', $companyId)->count();
                $section->status_id = 1;
            }

            $section->save();

            $actionLabel = $isNew ? "Created section" : "Updated section";
            static::logActivity("{$actionLabel}: {$section->name}", 'Sections');

            // Rendered inactive regardless of edit/create -- the browser
            // decides active/inactive styling itself right after patching
            // the DOM, since only it knows what's currently selected.
            return [
                'success'  => true,
                'isNew'    => $isNew,
                'tabHtml'  => self::renderTab($section, false),
                'sectionId' => IdEncoder::encode((int)$section->id),
                'messages' => ['Section saved successfully.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Delete a section and cascade-delete its questions (and their
     * options/fields), since an orphaned question with no section would be
     * unreachable from the UI anyway.
     */
    public function delete(?string $id): array
    {
        try {
            $currentUser = AuthService::currentUser();
            $companyId = $currentUser->company_id ?? 0;

            $rawId = $id ? IdEncoder::decode($id) : null;
            $section = $rawId ? Section::where('id', $rawId)->where('company_id', $companyId)->first() : null;
            if (!$section) {
                return ['success' => false, 'messages' => ['Section not found.']];
            }

            $name = $section->name;
            $questionIds = Question::where('section_id', $section->id)->pluck('id');

            QuestionOption::whereIn('question_id', $questionIds)->delete();
            QuestionField::whereIn('question_id', $questionIds)->delete();
            Question::where('section_id', $section->id)->delete();
            $section->delete();

            static::logActivity("Deleted section: {$name}", 'Sections');

            return ['success' => true, 'messages' => ['Section deleted successfully.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Persist the tab bar's new left-to-right order.
     */
    public function reorder(array $encodedIds): array
    {
        try {
            $currentUser = AuthService::currentUser();
            $companyId = $currentUser->company_id ?? 0;

            foreach ($encodedIds as $index => $encodedId) {
                $rawId = IdEncoder::decode((string)$encodedId);
                if (!$rawId) continue;
                Section::where('id', $rawId)->where('company_id', $companyId)->update(['pos_index' => $index]);
            }

            static::logActivity('Reordered sections', 'Sections');

            return ['success' => true, 'messages' => ['Section order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
