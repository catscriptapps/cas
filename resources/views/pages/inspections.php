<?php
// /resources/views/pages/inspections.php

declare(strict_types=1);

use Src\Service\AuthService;

if (!AuthService::currentUser() || empty(AuthService::currentUser()->company_id)) {
    include __DIR__ . '/auth-required.php';
    return;
}

$controller = new \Src\Controller\InspectionsController();
$controller->index();

$inspectionRows = $GLOBALS['inspectionRows'] ?? '';
$inspectionsCount = $GLOBALS['inspectionsCount'] ?? 0;
$inspectionsView = $GLOBALS['inspectionsView'] ?? 'current';
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Inspections' => '/inspections'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Inspections</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Create and manage inspection reports. Row color reflects each report's access-code expiry status.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <select id="inspections-view-select"
                class="rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2.5 pl-3 pr-8 text-sm font-bold text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 transition-all font-sans">
                <option value="current" <?= $inspectionsView === 'current' ? 'selected' : '' ?>>Current Inspections</option>
                <option value="archived" <?= $inspectionsView === 'archived' ? 'selected' : '' ?>>Archived Inspections</option>
            </select>

            <button type="button" id="add-inspection-btn" data-tooltip="Add Inspection"
                class="shrink-0 flex items-center justify-center rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden xs:inline md:inline">New Inspection</span>
            </button>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        <span>Status:</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm border border-black/10" style="background:#FFFFFF"></span> No Report Yet</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm border border-black/10" style="background:#AEFFAE"></span> Healthy (8+ days)</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm border border-black/10" style="background:#FFF78E"></span> Expiring Soon (3-7 days)</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm border border-black/10" style="background:#FF7272"></span> Expiring Imminently (0-2 days)</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm border border-black/10" style="background:#FFB6B6"></span> Expired</span>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="sticky top-[58px] sm:top-[62px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-4 text-left w-full md:w-[26%]">
                            <?php $sortColumn = 'property_address';
                            $sortLabel = 'Property Address';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden md:table-cell md:w-[15%]">
                            <?php $sortColumn = 'city';
                            $sortLabel = 'Location';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell lg:w-[15%]">
                            <?php $sortColumn = 'originator';
                            $sortLabel = 'Originator';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden sm:table-cell sm:w-[12%]">
                            <?php $sortColumn = 'access_code';
                            $sortLabel = 'Access Code';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell lg:w-[13%]">
                            <?php $sortColumn = 'expiry';
                            $sortLabel = 'Expiry';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell lg:w-[13%]">
                            <?php $sortColumn = 'last_saved';
                            $sortLabel = 'Last Saved';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="relative px-6 py-4 text-right w-20"><span class="sr-only">Actions</span></th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <?php $filterColumn = 'property_address';
                            $filterPlaceholder = 'Filter address…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden md:table-cell">
                            <?php $filterColumn = 'city';
                            $filterPlaceholder = 'Filter city…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'originator';
                            $filterPlaceholder = 'Filter originator…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden sm:table-cell">
                            <?php $filterColumn = 'access_code';
                            $filterPlaceholder = 'Filter code…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'expiry';
                            $filterPlaceholder = 'Filter expiry…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'last_saved';
                            $filterPlaceholder = 'Filter date…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5"></th>
                    </tr>
                </thead>
                <tbody id="inspections-tbody" data-total="<?= (int)($GLOBALS['inspectionsCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($inspectionRows)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="font-medium font-sans">No inspections found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $inspectionRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $footerCountName = 'inspections';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>
