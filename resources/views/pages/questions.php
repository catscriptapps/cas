<?php
// /resources/views/pages/questions.php

declare(strict_types=1);

use Src\Controller\SectionsController;
use Src\Controller\QuestionsController;

/** @var \App\Models\User|null $currentUser */

if (!$currentUser || empty($currentUser->company_id)) {
    include __DIR__ . '/auth-required.php';
    return;
}

$sectionsController = new SectionsController();
$sectionsController->index();

$sectionTabs = $GLOBALS['sectionTabs'] ?? '';
$sectionsCount = $GLOBALS['sectionsCount'] ?? 0;
$activeSectionEncodedId = $GLOBALS['activeSectionEncodedId'] ?? null;

$initialQuestionRows = '';
if ($activeSectionEncodedId) {
    $questionsController = new QuestionsController();
    $activeSection = SectionsController::forCurrentCompany()->firstWhere('id', \App\Utils\IdEncoder::decode($activeSectionEncodedId));
    if ($activeSection) {
        $activeSection->load(['questions.options', 'questions.fields']);
        foreach ($activeSection->questions as $question) {
            $initialQuestionRows .= QuestionsController::renderRow($question);
        }
    }
}
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Questions' => '/questions'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1 flex items-center gap-3">
            <div class="hidden sm:flex shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white items-center justify-center shadow-md shadow-primary-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Questions</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Build the question bank inspectors use to fill out your reports, organized into sections.
                </p>
            </div>
        </div>

        <button type="button" id="add-section-btn"
            class="shrink-0 flex items-center justify-center rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden xs:inline md:inline">Add Section</span>
        </button>
    </div>

    <div id="questions-sticky-bar" class="sticky top-[58px] sm:top-[62px] z-[30] bg-gray-50 dark:bg-slate-900 pt-1 pb-4 space-y-3">
        <div id="section-tabs-wrapper" class="flex flex-wrap gap-2.5" data-active-section-id="<?= htmlspecialchars((string)$activeSectionEncodedId) ?>">
            <?= $sectionTabs ?>
        </div>

        <div id="section-content-header" class="<?= $sectionsCount > 0 ? '' : 'hidden' ?> flex items-center justify-between bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl px-6 py-4">
            <div class="flex items-center gap-2">
                <h3 id="active-section-name" class="text-xs font-black text-primary-500 uppercase tracking-[0.3em]"></h3>
                <span id="questions-count" class="text-[10px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-950/30 px-2 py-0.5 rounded-md border border-primary-100 dark:border-primary-900/50">0</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="edit-active-section-btn" title="Edit Section"
                    class="p-2 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button type="button" id="delete-active-section-btn" title="Delete Section"
                    class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <span class="text-gray-200 dark:text-gray-800">|</span>
                <button type="button" id="add-question-btn"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-3.5 py-2 text-xs font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Question
                </button>
            </div>
        </div>
    </div>

    <div id="sections-empty-state" class="<?= $sectionsCount > 0 ? 'hidden' : '' ?> bg-white dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-800 rounded-2xl p-12 text-center">
        <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-primary-950/40 dark:to-secondary-950/40 flex items-center justify-center">
            <svg class="h-8 w-8 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
            </svg>
        </div>
        <p class="font-bold text-gray-700 dark:text-gray-300">No sections yet</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create your first section to start building your question bank.</p>
    </div>

    <div id="section-content" class="<?= $sectionsCount > 0 ? '' : 'hidden' ?> bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <div id="questions-list" class="space-y-3">
            <?= $initialQuestionRows ?>
        </div>

        <div id="questions-empty-state" class="<?= $initialQuestionRows !== '' ? 'hidden' : '' ?> py-12 text-center">
            <div class="mx-auto mb-3 w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-primary-950/40 dark:to-secondary-950/40 flex items-center justify-center">
                <svg class="h-7 w-7 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="font-bold text-gray-700 dark:text-gray-300 text-sm">No questions in this section yet</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click "Add Question" to build your first checklist item.</p>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 mb-2">
                <div class="flex items-center gap-3">
                    <h3 class="text-xs font-black text-secondary-500 uppercase tracking-[0.3em] flex items-center gap-2">
                        <span class="w-8 h-[2px] bg-secondary-500"></span>
                        Section Diagrams
                    </h3>
                    <span id="section-diagrams-count" class="text-[10px] font-bold text-secondary-600 bg-secondary-50 dark:bg-secondary-950/30 px-2 py-0.5 rounded-md border border-secondary-100 dark:border-secondary-900/50">0</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="delete-selected-section-diagrams-btn" data-tooltip="Delete Selected"
                        class="hidden items-center gap-1.5 rounded-lg bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-bold text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span id="delete-selected-section-diagrams-label">Delete Selected</span>
                    </button>
                    <button type="button" id="trigger-section-diagrams-upload"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-secondary-500 px-3.5 py-2 text-xs font-bold text-white shadow-md hover:bg-secondary-600 transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add Diagrams
                    </button>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mb-3">
                Optional full-page images (wiring schematics, layouts, etc.) added to the end of this section in every finished PDF report -- stretched to fill the page, like Cover Pages.
            </p>

            <div id="section-diagrams-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"></div>
        </div>
    </div>
</div>
