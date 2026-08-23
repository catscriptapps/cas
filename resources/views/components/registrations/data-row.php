<?php
// /resources/views/components/registrations/data-row.php

/** @var array $rowItem */
/** @var string $assetBase */

use Src\Service\AuthService;

$canManage = AuthService::isLoggedIn();

$fullName = $rowItem['full_name'] ?? trim(($rowItem['first_name'] ?? '') . ' ' . ($rowItem['last_name'] ?? ''));
if (empty($fullName)) $fullName = 'Unknown';

$hasPaid = (bool)($rowItem['has_paid'] ?? false);
$amountPaid = (float)($rowItem['amount_paid'] ?? 0);

$userDataAttrs = [
    'encoded-id'   => $rowItem['encoded_id'] ?? '',
    'full-name'    => $fullName,
    'first-name'   => $rowItem['first_name'] ?? '',
    'last-name'    => $rowItem['last_name'] ?? '',
    'email'        => $rowItem['email'] ?? '',
    'phone'        => $rowItem['phone'] ?? '',
    'city'         => $rowItem['city'] ?? '',
    'region-name'  => $rowItem['region_name'] ?? '',
    'division-name' => $rowItem['division_name'] ?? 'N/A',
    'desired-position' => $rowItem['desired_position'] ?? '',
    'team-name'    => $rowItem['team_name'] ?? '',
    'special-requests' => $rowItem['special_requests'] ?? '',
    'source-label' => $rowItem['source_label'] ?? '',
    'joined'       => $rowItem['created_at_formatted'] ?? 'N/A',
    'is-active'    => (int)($rowItem['status_id'] ?? 0) === 1 ? '1' : '0',
    'has-paid'     => $hasPaid ? '1' : '0',
    'amount-paid'  => number_format($amountPaid, 2, '.', ''),
];

$editClass = 'edit-registration-btn';
$deleteClass = 'delete-registration-btn';

$statusBadge = (int)($rowItem['status_id'] ?? 0) === 1
    ? '<span class="inline-flex items-center rounded-full bg-primary-50 dark:bg-primary-900/20 px-2.5 py-0.5 text-xs font-bold text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30">Current</span>'
    : '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>';

$paidBadge = $hasPaid
    ? '<span class="inline-flex items-center rounded-full bg-secondary-50 dark:bg-secondary-900/20 px-2.5 py-0.5 text-xs font-bold text-secondary-700 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-800/30">Paid</span>'
    : '<span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-900/20 px-2.5 py-0.5 text-xs font-bold text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800/30">Unpaid</span>';
?>

<tr id="registration-row-<?= $rowItem['entry_id'] ?? '0' ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?? '' ?>"
    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-b border-gray-100 dark:border-gray-800 font-sans">

    <td class="px-6 py-4 min-w-0">
        <div class="flex items-start lg:items-center min-w-0">
            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-lg">
                <?= strtoupper(substr($rowItem['first_name'] ?? 'U', 0, 1)) ?>
            </div>

            <div class="ml-4 flex-1 min-w-0">
                <div class="view-registration-trigger cursor-pointer block min-w-0"
                    <?php foreach ($userDataAttrs as $key => $val): ?>
                    data-<?= $key ?>='<?= htmlspecialchars((string)$val, ENT_QUOTES) ?>'
                    <?php endforeach; ?>>

                    <div class="flex items-center justify-between lg:block">
                        <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors truncate">
                            <?= htmlspecialchars($fullName) ?>
                        </div>
                        <div class="lg:hidden flex-shrink-0 ml-2">
                            <?= $statusBadge ?>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        <?= htmlspecialchars($rowItem['email'] ?? '') ?>
                    </div>
                    <div class="lg:hidden text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                        <?= htmlspecialchars($rowItem['division_name'] ?? 'N/A') ?>
                    </div>
                </div>

                <?php if ($canManage): ?>
                    <div class="mt-3 lg:hidden flex items-center gap-2">
                        <?php
                        $isMobile = true;
                        $dataAttrs = $userDataAttrs;
                        include __DIR__ . '/../ui/action-buttons.php';
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
            <?= htmlspecialchars($rowItem['division_name'] ?? 'N/A') ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-[11px] text-gray-600 dark:text-gray-400"><?= htmlspecialchars($rowItem['phone'] ?? 'N/A') ?></div>
        <div class="text-[11px] text-gray-400 dark:text-gray-500"><?= htmlspecialchars($rowItem['city'] ?? '') ?></div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-[11px] text-gray-600 dark:text-gray-400"><?= $rowItem['created_at_formatted'] ?? 'N/A' ?></div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="flex flex-col gap-0.5">
            <?= $paidBadge ?>
            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500">$<?= number_format($amountPaid, 2) ?></span>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <?= $statusBadge ?>
    </td>

    <td class="px-6 py-4 text-right hidden lg:table-cell">
        <?php if ($canManage): ?>
            <?php
            $isMobile = false;
            $dataAttrs = $userDataAttrs;
            include __DIR__ . '/../ui/action-buttons.php';
            ?>
        <?php endif; ?>
    </td>
</tr>
