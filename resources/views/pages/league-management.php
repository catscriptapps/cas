<?php
// /resources/views/pages/league-management.php

declare(strict_types=1);

use Src\Service\AuthService;

(new \Src\Controller\SportsController())->index();
(new \Src\Controller\LeaguesController())->index();
(new \Src\Controller\DivisionsController())->index();

$sportRows = $GLOBALS['sportRows'] ?? '';
$leagueRows = $GLOBALS['leagueRows'] ?? '';
$divisionRows = $GLOBALS['divisionRows'] ?? '';
$canManage = AuthService::isLoggedIn();
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['League Management' => '/league-management'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "League Management" + its NavigationConfig summary
    // (see layout-header.php).
    ?>

    <!-- Tabs -->
    <div id="league-mgmt-tabs" class="flex gap-1 border-b border-gray-200 dark:border-gray-800">
        <button type="button" data-tab-target="tab-sports" class="px-5 py-2.5 text-sm font-black rounded-t-xl border border-b-0 bg-white dark:bg-gray-900 text-primary-600 border-gray-200 dark:border-gray-800 z-10 -mb-[1px]">Sports</button>
        <button type="button" data-tab-target="tab-leagues" class="px-5 py-2.5 text-sm font-bold rounded-t-xl border border-b-0 bg-gray-100/80 dark:bg-gray-800/60 text-gray-400 border-transparent">Leagues</button>
        <button type="button" data-tab-target="tab-divisions" class="px-5 py-2.5 text-sm font-bold rounded-t-xl border border-b-0 bg-gray-100/80 dark:bg-gray-800/60 text-gray-400 border-transparent">Divisions</button>
    </div>

    <!-- Sports tab -->
    <div id="tab-sports" class="tab-pane block">
        <?php if ($canManage): ?>
            <div class="flex justify-end mb-4">
                <button type="button" id="add-sport-btn" class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Add Sport
                </button>
            </div>
        <?php endif; ?>
        <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
            <div class="w-full">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                    <thead class="sticky top-[84px] sm:top-[96px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-4 text-left w-full sm:w-[45%]">
                                <?php $sortColumn = 'sport';
                                $sortLabel = 'Sport';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[25%]">Leagues</th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[20%]">
                                <?php $sortColumn = 'status';
                                $sortLabel = 'Status';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="relative px-6 py-4 text-right w-24 hidden sm:table-cell"><span class="sr-only">Actions</span></th>
                        </tr>
                        <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                            <th class="px-6 py-2.5">
                                <?php $filterColumn = 'sport';
                                $filterPlaceholder = 'Filter sport…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                            <th class="px-6 py-2.5 hidden sm:table-cell">
                                <?php $filterColumn = 'status';
                                $filterPlaceholder = 'active / inactive…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                        </tr>
                    </thead>
                    <tbody id="sports-tbody" data-total="<?= (int)($GLOBALS['totalSportsCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        <?php if (empty($sportRows)): ?>
                            <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-medium">No sports found</td></tr>
                        <?php else: ?>
                            <?= $sportRows ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php $footerCountName = 'sports';
            include __DIR__ . '/../components/ui/footer-count.php'; ?>
        </div>
    </div>

    <!-- Leagues tab -->
    <div id="tab-leagues" class="tab-pane hidden">
        <?php if ($canManage): ?>
            <div class="flex justify-end mb-4">
                <button type="button" id="add-league-btn" class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Add League
                </button>
            </div>
        <?php endif; ?>
        <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
            <div class="w-full">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                    <thead class="sticky top-[84px] sm:top-[96px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-4 text-left w-full sm:w-[35%]">
                                <?php $sortColumn = 'league';
                                $sortLabel = 'League';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[20%]">Sport</th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[20%]">
                                <?php $sortColumn = 'divisions';
                                $sortLabel = 'Divisions';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[15%]">
                                <?php $sortColumn = 'status';
                                $sortLabel = 'Status';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="relative px-6 py-4 text-right w-24 hidden sm:table-cell"><span class="sr-only">Actions</span></th>
                        </tr>
                        <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                            <th class="px-6 py-2.5">
                                <?php $filterColumn = 'league';
                                $filterPlaceholder = 'Filter league or sport…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                            <th class="px-6 py-2.5 hidden sm:table-cell">
                                <?php $filterColumn = 'status';
                                $filterPlaceholder = 'active / inactive…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                        </tr>
                    </thead>
                    <tbody id="leagues-tbody" data-total="<?= (int)($GLOBALS['totalLeaguesCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        <?php if (empty($leagueRows)): ?>
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-medium">No leagues found</td></tr>
                        <?php else: ?>
                            <?= $leagueRows ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php $footerCountName = 'leagues';
            include __DIR__ . '/../components/ui/footer-count.php'; ?>
        </div>
    </div>

    <!-- Divisions tab -->
    <div id="tab-divisions" class="tab-pane hidden">
        <?php if ($canManage): ?>
            <div class="flex justify-end mb-4">
                <button type="button" id="add-division-btn" class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Add Division
                </button>
            </div>
        <?php endif; ?>
        <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
            <div class="w-full">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                    <thead class="sticky top-[84px] sm:top-[96px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-4 text-left w-full sm:w-[30%]">
                                <?php $sortColumn = 'division';
                                $sortLabel = 'Division';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[20%]">League</th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[15%]">Sport</th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[15%]">
                                <?php $sortColumn = 'price';
                                $sortLabel = 'Price';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="px-6 py-4 text-left hidden sm:table-cell w-[10%]">
                                <?php $sortColumn = 'status';
                                $sortLabel = 'Status';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="relative px-6 py-4 text-right w-24 hidden sm:table-cell"><span class="sr-only">Actions</span></th>
                        </tr>
                        <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                            <th class="px-6 py-2.5">
                                <?php $filterColumn = 'division';
                                $filterPlaceholder = 'Filter division or league…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                            <th class="px-6 py-2.5 hidden sm:table-cell">
                                <?php $filterColumn = 'status';
                                $filterPlaceholder = 'active / inactive…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden sm:table-cell"></th>
                        </tr>
                    </thead>
                    <tbody id="divisions-tbody" data-total="<?= (int)($GLOBALS['totalDivisionsCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        <?php if (empty($divisionRows)): ?>
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-medium">No divisions found</td></tr>
                        <?php else: ?>
                            <?= $divisionRows ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php $footerCountName = 'divisions';
            include __DIR__ . '/../components/ui/footer-count.php'; ?>
        </div>
    </div>
</div>
