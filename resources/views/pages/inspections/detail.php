<?php
// /resources/views/pages/inspections/detail.php

declare(strict_types=1);

use App\Models\Inspection;
use Src\Controller\InspectionsController;
use Src\Controller\InspectionDetailController;
use Src\Service\AuthService;

if (!AuthService::currentUser() || empty(AuthService::currentUser()->company_id)) {
    include __DIR__ . '/../auth-required.php';
    return;
}

$encodedId = $GLOBALS['encodedId'] ?? '';
$inspection = InspectionsController::findForCurrentUser($encodedId);

if (!$inspection) {
    http_response_code(404);
    ?>
    <div class="max-w-2xl mx-auto py-24 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inspection Not Found</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">It may have been deleted, or you may not have access to it.</p>
        <a href="<?= $baseUrl ?? '/' ?>inspections" data-partial class="inline-block mt-6 text-primary-600 font-bold hover:underline">&larr; Back to Inspections</a>
    </div>
    <?php
    return;
}

$detailController = new InspectionDetailController();
$detailController->index($inspection);

$sectionTabsHtml = $GLOBALS['sectionTabsHtml'] ?? '';
$activeSectionEncodedId = $GLOBALS['activeSectionEncodedId'] ?? '';
$activeSectionContentHtml = $GLOBALS['activeSectionContentHtml'] ?? '';
$encodedInspectionId = \App\Utils\IdEncoder::encode((int)$inspection->id);

$hasReport = $inspection->hasReport();
$rowColor = $inspection->rowColor();
$statusLabel = Inspection::labelForExpiry($inspection->date_expires?->format('Y-m-d H:i:s'));
?>

<div class="space-y-6 max-w-full py-10" data-inspection-id="<?= $encodedInspectionId ?>">
    <?php
    $breadcrumbs = ['Inspections' => '/inspections', $inspection->property_address => null];
    include __DIR__ . '/../../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="hidden sm:flex shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white items-center justify-center shadow-md shadow-primary-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans"><?= htmlspecialchars($inspection->property_address) ?></h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    <?= htmlspecialchars($inspection->city ?? '') ?><?= $inspection->region ? ', ' . htmlspecialchars($inspection->region->region) : '' ?>
                    <?= $inspection->postal_code ? ' &middot; ' . htmlspecialchars($inspection->postal_code) : '' ?>
                </p>
                <div class="mt-2 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wide" style="background:<?= $rowColor ?>;color:#000;">
                        <?= htmlspecialchars($statusLabel) ?>
                    </span>
                    <?php if ($hasReport): ?>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-500 dark:text-gray-400">
                            Access Code: <span class="font-mono"><?= htmlspecialchars($inspection->access_code) ?></span>
                            <button type="button" data-action="copy-access-code" data-code="<?= htmlspecialchars($inspection->access_code, ENT_QUOTES) ?>" title="Copy access code"
                                class="p-1 rounded text-current opacity-60 hover:opacity-100 hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            &middot; Expires <?= $inspection->date_expires->format('M j, Y') ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-start lg:items-end gap-1.5">
            <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= $baseUrl ?? '/' ?>inspections" data-partial title="Exit to Inspections"
                class="inline-flex items-center justify-center p-2 rounded-xl border-2 border-gray-200 dark:border-gray-700 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
            <?php if (!$hasReport): ?>
                <button type="button" id="edit-inspection-header-btn"
                    data-encoded-id="<?= $encodedInspectionId ?>"
                    data-property-address="<?= htmlspecialchars($inspection->property_address, ENT_QUOTES) ?>"
                    data-city="<?= htmlspecialchars($inspection->city ?? '', ENT_QUOTES) ?>"
                    data-country-id="<?= (int)($inspection->country_id ?? 0) ?>"
                    data-region-id="<?= (int)($inspection->region_id ?? 0) ?>"
                    data-postal-code="<?= htmlspecialchars($inspection->postal_code ?? '', ENT_QUOTES) ?>"
                    class="inline-flex items-center gap-1.5 rounded-xl border-2 border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Edit Header
                </button>
            <?php endif; ?>

            <?php if ($hasReport): ?>
                <button type="button" id="reopen-inspection-btn" data-id="<?= $encodedInspectionId ?>"
                    class="inline-flex items-center gap-1.5 rounded-xl border-2 border-amber-300 dark:border-amber-700 px-4 py-2 text-xs font-bold text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                    Reopen for Editing
                </button>
            <?php else: ?>
                <button type="button" id="finalize-inspection-btn" data-id="<?= $encodedInspectionId ?>"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-secondary-600 hover:bg-secondary-500 px-4 py-2 text-xs font-bold text-white shadow-md transition-colors">
                    Finalize Report
                </button>
            <?php endif; ?>

            <button type="button" id="delete-inspection-header-btn" data-id="<?= $encodedInspectionId ?>"
                class="inline-flex items-center gap-1.5 rounded-xl border-2 border-red-200 dark:border-red-900/50 px-4 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                Delete
            </button>
            </div>

            <?php if (!$hasReport): ?>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 text-left lg:text-right max-w-xs">
                    "Finalize Report" generates the PDF and starts the 14-day access-code window.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($hasReport && !empty($inspection->file_name)): ?>
        <div class="rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/30 px-4 py-3 text-xs font-semibold text-green-700 dark:text-green-400 flex items-center justify-between gap-3 flex-wrap">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                This report is finalized and locked. Reopen it for editing to make changes, including adding photos or videos.
            </span>
            <a href="<?= $baseUrl ?? '/' ?>pdfs/inspections/<?= (int)$inspection->company_id ?>/<?= htmlspecialchars($inspection->file_name) ?>" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 hover:bg-green-700 px-3 py-1.5 text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Download PDF
            </a>
        </div>
    <?php endif; ?>

    <div class="p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Inspection Summary</h3>
            <?php if (!$hasReport): $hintType = 'inspection_summary';
                include __DIR__ . '/../../components/ui/hint-trigger-button.php';
            endif; ?>
        </div>
        <textarea id="inspection-summary-textarea" rows="3" placeholder="Overall summary for this inspection..." <?= $hasReport ? 'readonly' : '' ?>
            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 text-gray-900 dark:text-white text-sm py-2.5 px-3 placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 resize-y"><?= htmlspecialchars($inspection->inspection_summary ?? '') ?></textarea>
        <span id="inspection-summary-save-indicator" class="hidden mt-1.5 text-[10px] font-bold text-green-600 dark:text-green-400">Saved</span>
    </div>

    <?php if (empty($sectionTabsHtml)): ?>
        <div class="text-center py-16 text-gray-400 dark:text-gray-500">
            <p class="font-bold text-sm">This company has no sections in its question bank yet.</p>
            <a href="<?= $baseUrl ?? '/' ?>questions" data-partial class="inline-block mt-3 text-primary-600 font-bold hover:underline">Go to Questions &rarr;</a>
        </div>
    <?php else: ?>
        <div id="inspection-sticky-bar" class="sticky top-[58px] sm:top-[62px] z-[30] bg-gray-50 dark:bg-slate-900 pt-1 pb-3 space-y-2">
            <!-- Compact vital-info strip: kept deliberately minimal (just status +
                 address, no action buttons) so it doesn't eat the whole screen on
                 mobile the way sticking the full header block did. -->
            <div class="flex items-center gap-2 bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-xl px-3.5 py-2">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wide shrink-0" style="background:<?= $rowColor ?>;color:#000;">
                    <?= htmlspecialchars($statusLabel) ?>
                </span>
                <?php
                $compactAddressParts = array_filter([
                    $inspection->property_address,
                    trim(implode(', ', array_filter([$inspection->city, $inspection->region?->abbreviation]))),
                ]);
                ?>
                <span class="text-sm font-bold text-gray-900 dark:text-white truncate min-w-0"><?= htmlspecialchars(implode(' - ', $compactAddressParts)) ?></span>
                <?php if ($hasReport): ?>
                    <span class="hidden sm:inline text-[11px] font-semibold text-gray-400 dark:text-gray-500 shrink-0 ml-auto">Expires <?= $inspection->date_expires->format('M j, Y') ?></span>
                <?php endif; ?>
            </div>

            <div id="inspection-section-tabs-wrapper" data-active-section-id="<?= $activeSectionEncodedId ?>" class="flex flex-wrap gap-2">
                <?= $sectionTabsHtml ?>
            </div>
        </div>

        <div id="inspection-section-content">
            <?= $activeSectionContentHtml ?>
        </div>
    <?php endif; ?>
</div>
