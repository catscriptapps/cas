<?php
// /resources/views/components/contacts/data-row.php

/** @var array $rowItem */

use Src\Service\AuthService;

$canManage = AuthService::isLoggedIn();
$fullName = $rowItem['full_name'] ?? 'Unknown';
$initial = strtoupper(substr($fullName, 0, 1));
$isEmergency = (int)($rowItem['is_emergency'] ?? 0) === 1;

// Badge color keyed off role_id -- same visual mapping as legacy cas-sports.
$badgeClasses = match ((int)($rowItem['role_id'] ?? 0)) {
    1 => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
    2 => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    3 => 'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800',
    4 => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
    default => 'bg-gray-50 text-gray-700 border-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700',
};

$contactDataAttrs = [
    'encoded-id'   => $rowItem['encoded_id'] ?? '',
    'full-name'    => $fullName,
    'organization' => $rowItem['organization'] ?? '',
    'email'        => $rowItem['email'] ?? '',
    'phone'        => $rowItem['phone'] ?? '',
    'leagues'      => $rowItem['leagues'] ?? 'All',
    'is-emergency' => $isEmergency ? '1' : '0',
    'role-id'      => $rowItem['role_id'] ?? '',
];

$editClass = 'edit-contact-btn';
$deleteClass = 'delete-contact-btn';
?>
<tr id="contact-row-<?= $rowItem['entry_id'] ?? '0' ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?? '' ?>"
    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-b border-gray-100 dark:border-gray-800 font-sans">

    <td class="px-6 py-4 min-w-0">
        <div class="flex items-start lg:items-center min-w-0">
            <div class="relative flex-shrink-0">
                <div class="h-10 w-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-lg">
                    <?= $initial ?>
                </div>
                <?php if ($isEmergency): ?>
                    <div class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full border-2 border-white dark:border-gray-900 flex items-center justify-center" title="Emergency Contact">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11.603 7.963a.75.75 0 00-.977-1.138l-4.5 3.857a.75.75 0 000 1.138l4.5 3.857a.75.75 0 00.977-1.138L7.761 11l3.842-3.037z" />
                        </svg>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ml-4 flex-1 min-w-0 overflow-hidden">
                <div class="view-contact-trigger cursor-pointer group/title block min-w-0"
                    data-id="<?= $rowItem['encoded_id'] ?? '' ?>"
                    data-name="<?= htmlspecialchars($fullName) ?>"
                    data-org="<?= htmlspecialchars($rowItem['organization'] ?? 'Independent') ?>"
                    data-email="<?= htmlspecialchars($rowItem['email'] ?? '—') ?>"
                    data-phone="<?= htmlspecialchars($rowItem['phone'] ?? '—') ?>"
                    data-leagues="<?= htmlspecialchars($rowItem['leagues'] ?? 'All') ?>"
                    data-emergency="<?= $isEmergency ? 'Yes' : 'No' ?>"
                    data-role="<?= htmlspecialchars($rowItem['role_label'] ?? 'Official') ?>">

                    <div class="text-sm font-bold text-gray-900 dark:text-white group-hover/title:text-primary-600 transition-colors truncate">
                        <?= htmlspecialchars($fullName) ?>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        <?= htmlspecialchars($rowItem['organization'] ?: ($rowItem['email'] ?: 'No Organization')) ?>
                    </div>

                    <div class="lg:hidden mt-1.5 flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold uppercase <?= $badgeClasses ?> px-1.5 py-0.5 rounded-md"><?= htmlspecialchars($rowItem['role_label'] ?? 'Official') ?></span>
                        <?php if ($isEmergency): ?>
                            <span class="text-[10px] font-bold text-red-600 uppercase">Emergency</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($canManage): ?>
                    <?php
                    $dataAttrs = $contactDataAttrs;
                    $isMobile = true;
                    include __DIR__ . '/../ui/action-buttons.php';
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell min-w-0">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
            <?= htmlspecialchars($rowItem['leagues'] ?: 'All') ?>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">Assigned Leagues</div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell text-sm text-gray-700 dark:text-gray-300">
        <?= htmlspecialchars($rowItem['phone'] ?: '—') ?>
    </td>

    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold border <?= $badgeClasses ?>">
            <?= htmlspecialchars($rowItem['role_label'] ?? 'Official') ?>
        </span>
    </td>

    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
        <?php if ($isEmergency): ?>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900">
                Priority
            </span>
        <?php else: ?>
            <span class="text-xs text-gray-400 font-medium italic">Standard</span>
        <?php endif; ?>
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-right hidden lg:table-cell">
        <?php if ($canManage): ?>
            <?php
            $dataAttrs = $contactDataAttrs;
            $isMobile = false;
            include __DIR__ . '/../ui/action-buttons.php';
            ?>
        <?php endif; ?>
    </td>
</tr>
