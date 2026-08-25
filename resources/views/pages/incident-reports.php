<?php
// /resources/views/pages/incident-reports.php

declare(strict_types=1);

use Src\Service\AuthService;

$controller = new \Src\Controller\IncidentReportsController();
$controller->index();

$incidentRows = $GLOBALS['incidentRows'] ?? '';
$canManage = AuthService::isLoggedIn();
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Incident Reports' => '/incident-reports'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Incident Reports</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Official documentation of on-floor incidents, disciplinary actions, and medical assistance.
            </p>
        </div>

        <?php if ($canManage): ?>
            <button type="button" id="add-incident-btn"
                class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                File Report
            </button>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="sticky top-[84px] sm:top-[96px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-4 text-left w-full lg:w-[22%]">
                            <?php $sortColumn = 'date';
                            $sortLabel = 'Date';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[22%]">
                            <?php $sortColumn = 'location';
                            $sortLabel = 'Location';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell">
                            <?php $sortColumn = 'teams';
                            $sortLabel = 'Teams Involved';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[120px]">
                            <?php $sortColumn = 'status';
                            $sortLabel = 'Status';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="relative px-6 py-4 text-right w-24 hidden lg:table-cell">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <?php $filterColumn = 'report';
                            $filterPlaceholder = 'Filter location, teams, persons…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'location';
                            $filterPlaceholder = 'Filter location…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'teams';
                            $filterPlaceholder = 'Filter teams…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'status';
                            $filterPlaceholder = 'filed / draft…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                    </tr>
                </thead>
                <tbody id="incidents-tbody" data-total="<?= (int)($GLOBALS['totalIncidentsCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($incidentRows)): ?>
                        <tr class="empty-state-row">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <p class="font-medium font-sans">No incident reports found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $incidentRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $footerCountName = 'incidents';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/incident-reports/view-report-modal.php'; ?>
