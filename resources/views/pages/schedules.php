<?php
// /resources/views/pages/schedules.php

declare(strict_types=1);

use Src\Service\AuthService;

$controller = new \Src\Controller\SeasonsController();
$controller->index();

$seasonRows = $GLOBALS['seasonRows'] ?? '';
$canManage = AuthService::isLoggedIn();
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Schedules' => '/schedules'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "Schedules" + its NavigationConfig summary for every
    // viewer, admin included (see layout-header.php).
    ?>
    <?php if ($canManage): ?>
        <div class="flex justify-end">
            <button type="button" id="add-season-btn"
                class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Season
            </button>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="sticky top-[84px] sm:top-[96px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-4 text-left w-full sm:w-[55%]">
                            <?php $sortColumn = 'season';
                            $sortLabel = 'Division / Season';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden sm:table-cell w-[20%]">
                            <?php $sortColumn = 'status';
                            $sortLabel = 'Status';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="relative px-6 py-4 text-right w-[25%] hidden sm:table-cell"><span class="sr-only">Actions</span></th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <?php $filterColumn = 'season';
                            $filterPlaceholder = 'Filter division or year…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden sm:table-cell">
                            <?php $filterColumn = 'status';
                            $filterPlaceholder = 'active / inactive…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                    </tr>
                </thead>
                <tbody id="seasons-tbody" data-total="<?= (int)($GLOBALS['totalSeasonsCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($seasonRows)): ?>
                        <tr class="empty-state-row">
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <p class="font-bold text-lg font-sans">No seasons scheduled</p>
                                    <p class="text-sm font-sans">Select a division and year above to create a new season.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $seasonRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $footerCountName = 'seasons';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/seasons/view-teams-modal.php'; ?>
