<?php
// /resources/views/components/league-management/division-row.php

/** @var array $rowItem */

use Src\Service\AuthService;

$canManage = AuthService::isLoggedIn();

$isActive = (int)($rowItem['status_id'] ?? 0) === 1;
$statusBadge = $isActive
    ? '<span class="inline-flex items-center rounded-full bg-primary-50 dark:bg-primary-900/20 px-2.5 py-0.5 text-xs font-bold text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30">Active</span>'
    : '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Inactive</span>';

$price = number_format((float)($rowItem['price'] ?? 0), 2);

$dataAttrs = [
    'encoded-id' => $rowItem['encoded_id'] ?? '',
    'division'   => $rowItem['division'] ?? '',
    'league-id'  => $rowItem['league_id'] ?? '',
    'price'      => $rowItem['price'] ?? 0,
    'is-active'  => $isActive ? '1' : '0',
];
?>
<tr id="division-row-<?= $rowItem['division_id'] ?? '0' ?>" data-encoded-id="<?= $rowItem['encoded_id'] ?? '' ?>" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group font-sans">
    <td class="px-6 py-4">
        <div class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($rowItem['division'] ?? '') ?></div>
        <div class="text-xs text-gray-400 sm:hidden"><?= htmlspecialchars($rowItem['sport_name'] ?? 'N/A') ?> &middot; $<?= $price ?></div>
    </td>
    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($rowItem['league_name'] ?? 'N/A') ?></td>
    <td class="px-6 py-4 hidden sm:table-cell">
        <span class="inline-flex items-center rounded-md bg-secondary-50 dark:bg-secondary-900/20 px-2 py-0.5 text-xs font-bold text-secondary-700 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-800/30"><?= htmlspecialchars($rowItem['sport_name'] ?? 'N/A') ?></span>
    </td>
    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">$<?= $price ?></td>
    <td class="px-6 py-4"><?= $statusBadge ?></td>
    <td class="px-6 py-4 text-right">
        <?php if ($canManage): ?>
            <?php
            $editClass = 'edit-division-btn';
            $deleteClass = 'delete-division-btn';
            $isMobile = false;
            include __DIR__ . '/../ui/action-buttons.php';
            ?>
        <?php endif; ?>
    </td>
</tr>
