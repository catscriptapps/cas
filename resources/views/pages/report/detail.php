<?php
// /resources/views/pages/report/detail.php
//
// Public, unauthenticated page reached from the guest home page's "Get My
// Report" access-code form. Resolves the code and, if it's a still-current
// finalized report, renders the full inspection detail in read-only mode --
// the same section tabs, photos, and videos the company sees, minus every
// edit control -- so a client can review everything without an account.
// Section videos aren't included in the generated PDF at all, which is the
// whole reason this page shows the live detail view instead of just linking
// to the download.

declare(strict_types=1);

use Src\Controller\InspectionsController;
use Src\Controller\InspectionDetailController;

$accessCode = strtoupper(trim((string)($GLOBALS['encodedId'] ?? '')));
$inspection = InspectionsController::findByAccessCode($accessCode);
?>

<?php if (!$inspection): ?>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-16 lg:py-24 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">
        <div class="text-center">
            <div class="mx-auto mb-6 w-16 h-16 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <h1 class="text-2xl font-black text-secondary-900 dark:text-white mb-3">Report Not Found</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                We couldn't find a current report for that access code. It may have been entered incorrectly, or the report's 14-day access window may have expired.
            </p>
            <a href="<?= $baseUrl ?? '/' ?>home" data-partial class="inline-block mt-8 text-primary-600 font-bold hover:underline">&larr; Back to Home</a>
        </div>
    </div>
<?php else: ?>
    <?php
    $company = $inspection->company;
    $fullAddress = trim(implode(', ', array_filter([
        $inspection->city,
        $inspection->region?->region,
        $inspection->postal_code,
        $inspection->country?->country,
    ])));
    $pdfUrl = ($baseUrl ?? '/') . 'pdfs/inspections/' . (int)$inspection->company_id . '/' . rawurlencode((string)$inspection->file_name);

    $detailController = new InspectionDetailController();
    $detailController->index($inspection, false);

    $sectionTabsHtml = $GLOBALS['sectionTabsHtml'] ?? '';
    $activeSectionEncodedId = $GLOBALS['activeSectionEncodedId'] ?? '';
    $activeSectionContentHtml = $GLOBALS['activeSectionContentHtml'] ?? '';
    ?>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 space-y-6 font-sans animate-in fade-in slide-in-from-bottom-4 duration-700">
        <?php
        // Guest mode has no "Inspections" list to hop back through (there's
        // nothing for a client to browse besides this one report), so the
        // breadcrumb here is just Home -> Property Address instead of the
        // staff page's Home -> Inspections -> Property Address.
        $breadcrumbs = [$inspection->property_address => null];
        $breadcrumbHome = ['label' => 'Home', 'url' => ($baseUrl ?? '/') . 'home', 'title' => 'Home', 'summary' => ''];
        include __DIR__ . '/../../components/ui/breadcrumbs.php';
        ?>

        <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-primary-600 mb-1"><?= htmlspecialchars($company?->company_name ?? '') ?></p>
                    <h1 class="text-2xl font-black text-secondary-900 dark:text-white"><?= htmlspecialchars($inspection->property_address) ?></h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($fullAddress) ?></p>
                    <p class="mt-2 text-xs font-semibold text-gray-400 dark:text-gray-500">
                        Available until <?= htmlspecialchars($inspection->date_expires->format('F j, Y')) ?>
                    </p>
                </div>

                <?php if (!empty($inspection->file_name)): ?>
                    <a href="<?= $pdfUrl ?>" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 hover:bg-primary-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary-500/30 transition-all active:scale-95 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($inspection->inspection_summary)): ?>
            <div class="p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400 mb-2">Inspection Summary</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"><?= htmlspecialchars($inspection->inspection_summary) ?></p>
            </div>
        <?php endif; ?>

        <?php if (empty($sectionTabsHtml)): ?>
            <div class="text-center py-16 text-gray-400 dark:text-gray-500">
                <p class="font-bold text-sm">This report has no sections yet.</p>
            </div>
        <?php else: ?>
            <div id="report-section-tabs-wrapper" data-active-section-id="<?= $activeSectionEncodedId ?>"
                data-access-code="<?= htmlspecialchars($inspection->access_code) ?>" class="flex flex-wrap gap-2">
                <?= $sectionTabsHtml ?>
            </div>

            <div id="report-section-content">
                <?= $activeSectionContentHtml ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
