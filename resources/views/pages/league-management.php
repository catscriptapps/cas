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

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">League Management</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Manage the sports, leagues, and divisions registrants can sign up for.
        </p>
    </div>

    <!-- Tabs -->
    <div id="league-mgmt-tabs" class="flex gap-1 border-b border-gray-200 dark:border-gray-800">
        <button type="button" data-tab-target="tab-sports" class="px-5 py-2.5 text-sm font-black rounded-t-xl border border-b-0 bg-white dark:bg-gray-900 text-primary-600 border-gray-200 dark:border-gray-800 z-10 -mb-[1px]">Sports</button>
        <button type="button" data-tab-target="tab-leagues" class="px-5 py-2.5 text-sm font-bold rounded-t-xl border border-b-0 bg-gray-100/80 dark:bg-gray-800/60 text-gray-400 border-transparent">Leagues</button>
        <button type="button" data-tab-target="tab-divisions" class="px-5 py-2.5 text-sm font-bold rounded-t-xl border border-b-0 bg-gray-100/80 dark:bg-gray-800/60 text-gray-400 border-transparent">Divisions</button>
    </div>

    <!-- Sports tab -->
    <div id="tab-sports" class="tab-pane block">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <input type="text" id="sports-search" placeholder="Search sports…" class="w-full sm:max-w-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm py-2 px-3.5 focus:border-primary-400 focus:ring-primary-400 transition-colors">
            <?php if ($canManage): ?>
                <button type="button" id="add-sport-btn" class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Add Sport
                </button>
            <?php endif; ?>
        </div>
        <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 min-w-[500px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sport</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Leagues</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="sports-tbody" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?= $sportRows ?>
                </tbody>
            </table>
        </div>
        <div class="px-1 pt-3">
            <p id="sports-count" class="text-sm text-gray-600 dark:text-gray-400 font-medium"><?= (int)($GLOBALS['totalSportsCount'] ?? 0) ?> sports</p>
        </div>
    </div>

    <!-- Leagues tab -->
    <div id="tab-leagues" class="tab-pane hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <input type="text" id="leagues-search" placeholder="Search leagues…" class="w-full sm:max-w-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm py-2 px-3.5 focus:border-primary-400 focus:ring-primary-400 transition-colors">
            <?php if ($canManage): ?>
                <button type="button" id="add-league-btn" class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Add League
                </button>
            <?php endif; ?>
        </div>
        <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">League</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sport</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Divisions</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="leagues-tbody" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?= $leagueRows ?>
                </tbody>
            </table>
        </div>
        <div class="px-1 pt-3">
            <p id="leagues-count" class="text-sm text-gray-600 dark:text-gray-400 font-medium"><?= (int)($GLOBALS['totalLeaguesCount'] ?? 0) ?> leagues</p>
        </div>
    </div>

    <!-- Divisions tab -->
    <div id="tab-divisions" class="tab-pane hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <input type="text" id="divisions-search" placeholder="Search divisions…" class="w-full sm:max-w-xs rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm py-2 px-3.5 focus:border-primary-400 focus:ring-primary-400 transition-colors">
            <?php if ($canManage): ?>
                <button type="button" id="add-division-btn" class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Add Division
                </button>
            <?php endif; ?>
        </div>
        <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 min-w-[700px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Division</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">League</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Sport</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="divisions-tbody" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?= $divisionRows ?>
                </tbody>
            </table>
        </div>
        <div class="px-1 pt-3">
            <p id="divisions-count" class="text-sm text-gray-600 dark:text-gray-400 font-medium"><?= (int)($GLOBALS['totalDivisionsCount'] ?? 0) ?> divisions</p>
        </div>
    </div>
</div>
