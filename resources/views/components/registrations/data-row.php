<?php
// /resources/views/components/registrations/data-row.php

/** @var array $rowItem */
/** @var string $assetBase */

use Src\Service\AuthService;

$canManage = AuthService::isLoggedIn();

$fullName = $rowItem['full_name'] ?? 'Unknown';
$hasPaid = (int)($rowItem['has_paid'] ?? 0) === 1;
$amountPaid = (float)($rowItem['amount_paid'] ?? 0);
$sourceLabel = $rowItem['source_label'] ?: '—';
$specialRequests = $rowItem['special_requests'] ?: '—';

$userDataAttrs = [
    'encoded-id'   => $rowItem['encoded_id'] ?? '',
    'full-name'    => $fullName,
    'email'        => $rowItem['email'] ?? '',
    'phone'        => $rowItem['phone'] ?? '',
    'city'         => $rowItem['city'] ?? '',
    'province-name' => $rowItem['province_name'] ?? '',
    'division-name' => $rowItem['division_name'] ?? 'N/A',
    'position'     => $rowItem['position'] ?? '',
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

// Shape/size/colors match the legacy cas-sports registrations table exactly.
// Rendered as a button (when the viewer can manage records) so a single
// click flips paid/unpaid directly from the row -- no need to open the
// full edit modal just to toggle payment.
$paidBadgeClasses = 'inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-black border uppercase w-fit transition-opacity '
    . ($hasPaid
        ? 'bg-secondary-50 text-secondary-700 border-secondary-200 dark:bg-secondary-900/30 dark:text-secondary-400 dark:border-secondary-800'
        : 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-900');

if ($canManage) {
    $paidBadge = '<button type="button" class="toggle-paid-btn ' . $paidBadgeClasses . ' hover:opacity-70 active:scale-95 cursor-pointer"'
        . ' data-encoded-id="' . htmlspecialchars($rowItem['encoded_id'] ?? '', ENT_QUOTES) . '"'
        . ' data-has-paid="' . ($hasPaid ? '1' : '0') . '"'
        . ' title="Click to mark as ' . ($hasPaid ? 'Unpaid' : 'Paid') . '">'
        . ($hasPaid ? 'Paid' : 'Unpaid') . '</button>';
} else {
    $paidBadge = '<span class="' . $paidBadgeClasses . '">' . ($hasPaid ? 'Paid' : 'Unpaid') . '</span>';
}

// Division/Position color bar -- mirrors legacy's division-name-based color
// coding (teal for a "15" division, lime for "18"), defaulting to gray for
// everything else, which is what every division in this project's own
// naming scheme resolves to today.
$divSlug = strtolower($rowItem['division_name'] ?? '');
$divColorClass = 'bg-gray-400';
if (str_contains($divSlug, '15')) {
    $divColorClass = 'bg-teal-500';
} elseif (str_contains($divSlug, '18')) {
    $divColorClass = 'bg-lime-500';
}

// Subliminal row tint echoing the payment badge's own color family -- barely
// there at rest, a touch stronger on hover. Recomputed on every render, so
// the toggle-paid click handler's outerHTML swap (see toggle-paid.js) flips
// this along with the badge for free, with no separate JS needed.
$rowBgClass = $hasPaid
    ? 'bg-secondary-50/60 dark:bg-secondary-500/[0.06] hover:bg-secondary-100/70 dark:hover:bg-secondary-500/[0.12]'
    : 'bg-red-50/60 dark:bg-red-500/[0.06] hover:bg-red-100/70 dark:hover:bg-red-500/[0.12]';
?>

<tr id="registration-row-<?= $rowItem['entry_id'] ?? '0' ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?? '' ?>"
    class="<?= $rowBgClass ?> transition-colors group border-b border-gray-100 dark:border-gray-800 font-sans">

    <!-- Player Info: name / email / phone -->
    <td class="px-6 py-4 min-w-0">
        <div class="flex items-start lg:items-center min-w-0">
            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-lg">
                <?= strtoupper(substr($fullName, 0, 1)) ?>
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
                    <div class="text-xs text-gray-500 dark:text-gray-400 break-words">
                        <?= htmlspecialchars($rowItem['email'] ?? '') ?>
                    </div>
                    <div class="text-xs text-primary-600 dark:text-primary-400 font-medium">
                        <?= htmlspecialchars($rowItem['phone'] ?: 'No Phone') ?>
                    </div>
                    <div class="lg:hidden flex items-center justify-between gap-2 mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= htmlspecialchars($rowItem['division_name'] ?? 'N/A') ?></span>
                        <span class="flex items-center gap-1.5 shrink-0">
                            <?= $paidBadge ?>
                            <span class="text-[11px] font-bold text-gray-900 dark:text-white">$<?= number_format($amountPaid, 2) ?></span>
                        </span>
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

    <!-- Division / Position -->
    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-1 h-7 rounded-full <?= $divColorClass ?> opacity-75"></div>
            <div class="flex flex-col">
                <span class="text-sm font-black text-gray-800 dark:text-white leading-tight uppercase">
                    <?= htmlspecialchars($rowItem['division_name'] ?? 'N/A') ?>
                </span>
                <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                    <?= htmlspecialchars($rowItem['position'] ?: 'N/A') ?>
                </span>
            </div>
        </div>
    </td>

    <!-- Heard About Us / Special Requests -- shown in full, never truncated:
         the source on its own line, then the special-requests note beneath
         it in smaller italic type, both wrapping to as many lines as they
         need. -->
    <td class="px-6 py-4 hidden lg:table-cell align-top">
        <div class="text-sm text-gray-900 dark:text-gray-100 break-words">
            <?= htmlspecialchars($sourceLabel) ?>
        </div>
        <div class="text-xs text-gray-600 dark:text-gray-400 italic break-words mt-0.5">
            <?= htmlspecialchars($specialRequests) ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-[11px] text-gray-600 dark:text-gray-400"><?= $rowItem['created_at_formatted'] ?? 'N/A' ?></div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell whitespace-nowrap">
        <div class="flex flex-col">
            <?= $paidBadge ?>
            <div class="text-[11px] font-bold text-gray-900 dark:text-white mt-1">$<?= number_format($amountPaid, 2) ?></div>
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
