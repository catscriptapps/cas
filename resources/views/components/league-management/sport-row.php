<?php
// /resources/views/components/league-management/sport-row.php

/** @var array $rowItem */

use Src\Service\AuthService;

$canManage = AuthService::isLoggedIn();

$isActive = (int)($rowItem['status_id'] ?? 0) === 1;
$statusBadge = $isActive
    ? '<span class="inline-flex items-center rounded-full bg-primary-50 dark:bg-primary-900/20 px-2.5 py-0.5 text-xs font-bold text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30">Active</span>'
    : '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Inactive</span>';

$dataAttrs = [
    'encoded-id' => $rowItem['encoded_id'] ?? '',
    'sport-name' => $rowItem['sport_name'] ?? '',
    'is-active'  => $isActive ? '1' : '0',
];
?>
<tr id="sport-row-<?= $rowItem['sport_id'] ?? '0' ?>" data-encoded-id="<?= $rowItem['encoded_id'] ?? '' ?>" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group font-sans">
    <td class="px-6 py-4">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-9 w-9 shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm">
                    <?= strtoupper(substr($rowItem['sport_name'] ?? 'S', 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($rowItem['sport_name'] ?? '') ?></div>
                    <div class="text-xs text-gray-400 sm:hidden"><?= (int)($rowItem['league_count'] ?? 0) ?> leagues &middot; <?= $statusBadge ?></div>
                </div>
            </div>
            <?php if ($canManage): ?>
                <div class="sm:hidden shrink-0">
                    <?php
                    $editClass = 'edit-sport-btn';
                    $deleteClass = 'delete-sport-btn';
                    $isMobile = true;
                    include __DIR__ . '/../ui/action-buttons.php';
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </td>
    <td class="px-6 py-4 hidden sm:table-cell text-sm text-gray-600 dark:text-gray-400"><?= (int)($rowItem['league_count'] ?? 0) ?></td>
    <td class="px-6 py-4 hidden sm:table-cell"><?= $statusBadge ?></td>
    <td class="px-6 py-4 text-right hidden sm:table-cell">
        <?php if ($canManage): ?>
            <?php
            $editClass = 'edit-sport-btn';
            $deleteClass = 'delete-sport-btn';
            $isMobile = false;
            include __DIR__ . '/../ui/action-buttons.php';
            ?>
        <?php endif; ?>
    </td>
</tr>
