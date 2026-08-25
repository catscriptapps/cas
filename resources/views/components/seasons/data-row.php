<?php
// /resources/views/components/seasons/data-row.php

use Src\Service\AuthService;

/** @var array $rowItem */
/** @var string $pageContext */

$pageContext = $pageContext ?? 'schedules';
$contextConfig = [
    'schedules' => ['class' => 'view-schedule-trigger', 'label' => 'View'],
    'stats'     => ['class' => 'view-stats-trigger',    'label' => 'View'],
];
$currentConfig = $contextConfig[$pageContext] ?? $contextConfig['schedules'];

$canManage = AuthService::isLoggedIn();
$initial = strtoupper(substr($rowItem['division'] ?? 'U', 0, 1));
$teamCount = $rowItem['team_count'] ?? 0;
$isActive = (int)($rowItem['status_id'] ?? 0) === 1;

$dataAttrs = [
    'season-id' => $rowItem['season_id'] ?? 0,
    'encoded-id' => $rowItem['encoded_id'] ?? '',
    'division-id' => $rowItem['division_id'] ?? 0,
    'division-name' => $rowItem['division'] ?? '',
    'season-year' => $rowItem['season_year'] ?? '',
    'status-id' => $rowItem['status_id'] ?? 1,
    'teams' => json_encode($rowItem['teams'] ?? []),
];

$dataString = '';
foreach ($dataAttrs as $key => $val) {
    $dataString .= ' data-' . $key . '="' . htmlspecialchars((string)$val) . '"';
}
?>
<tr id="season-row-<?= $rowItem['season_id'] ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?>"
    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-b border-gray-100 dark:border-gray-800 font-sans group">

    <td class="px-6 py-4">
        <div class="flex items-center">
            <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-lg">
                <?= $initial ?>
            </div>
            <div class="ml-4 min-w-0">
                <div class="text-sm font-bold text-gray-900 dark:text-white truncate">
                    <span class="hidden lg:inline cursor-pointer hover:text-primary-600 transition-colors <?= $currentConfig['class'] ?>" <?= $dataString ?>>
                        <?= htmlspecialchars($rowItem['division'] ?? 'Unknown') ?>
                    </span>
                    <span class="inline lg:hidden">
                        <?= htmlspecialchars($rowItem['division'] ?? 'Unknown') ?>
                    </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Season Year: <?= htmlspecialchars($rowItem['season_year'] ?? 'N/A') ?>
                </div>

                <div class="flex flex-wrap items-center gap-3 mt-3 lg:hidden">
                    <button type="button" class="<?= $currentConfig['class'] ?> text-[10px] font-bold text-primary-600 flex items-center gap-1.5 uppercase tracking-widest bg-primary-50 dark:bg-primary-900/20 px-2.5 py-1.5 rounded-lg border border-primary-100 dark:border-primary-800" <?= $dataString ?>>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <?= $currentConfig['label'] ?>
                    </button>

                    <?php if ($canManage): ?>
                        <button type="button" class="view-teams-trigger text-[10px] font-bold text-secondary-700 flex items-center gap-1.5 uppercase tracking-widest bg-secondary-100 dark:bg-secondary-900/30 px-2.5 py-1.5 rounded-lg border border-secondary-200 dark:border-secondary-800" <?= $dataString ?>>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?= $teamCount ?> Teams
                        </button>

                        <button type="button" class="delete-season-btn text-[10px] font-bold text-red-600 flex items-center gap-1.5 uppercase tracking-widest bg-red-50 dark:bg-red-900/20 px-2.5 py-1.5 rounded-lg border border-red-100 dark:border-red-900/30" <?= $dataString ?>>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 hidden sm:table-cell">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold border
            <?= $isActive
                ? 'bg-green-50 text-green-700 border-green-100 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800'
                : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' ?>">
            <?= $isActive ? 'Active' : 'Inactive' ?>
        </span>
    </td>

    <td class="px-6 py-4 text-right hidden sm:table-cell">
        <div class="flex justify-end items-center space-x-3">
            <?php if ($canManage): ?>
                <button type="button"
                    class="view-teams-trigger inline-flex items-center gap-2 px-3 py-2 text-[11px] font-bold text-secondary-700 bg-secondary-100 dark:bg-secondary-900/30 border border-secondary-200 dark:border-secondary-800 rounded-xl uppercase tracking-widest hover:bg-secondary-200 transition-colors"
                    <?= $dataString ?>>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <?= $teamCount ?> Teams
                </button>
            <?php endif ?>

            <button type="button"
                class="<?= $currentConfig['class'] ?> inline-flex items-center gap-2 px-3 py-2 text-[11px] font-bold text-primary-600 bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 rounded-xl uppercase tracking-widest hover:bg-primary-100 transition-colors"
                <?= $dataString ?>>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <?= $currentConfig['label'] ?>
            </button>

            <?php if ($canManage): ?>
                <button type="button" title="Remove Division" class="delete-season-btn inline-flex text-[10px] font-bold text-red-600 items-center gap-1.5 uppercase tracking-widest bg-red-50 dark:bg-red-900/20 px-2.5 py-1.5 rounded-lg border border-red-100 dark:border-red-900/30" <?= $dataString ?>>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            <?php endif ?>
        </div>
    </td>
</tr>
