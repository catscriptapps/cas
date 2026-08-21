<?php
// /resources/views/components/companies/data-row.php

/** @var array $rowItem */
/** @var string $assetBase */

use Src\Service\AuthService;

$isAdmin = AuthService::isAdmin();
// A Company Admin only ever sees their own company row here (scoped by
// CompaniesController::index()), so being a Company Admin at all is enough
// to know this is their own company -- they can edit it, just not delete it.
$canEdit = $isAdmin || AuthService::isCompanyAdmin();

$companyName = $rowItem['company_name'] ?? 'Unnamed Company';

$hasLogo = !empty($rowItem['logo_url']);
$LOGO_DIR_PREFIX = $assetBase . 'images/uploads/company-logos/';
$logoUrl = $hasLogo ? htmlspecialchars($LOGO_DIR_PREFIX . $rowItem['logo_url']) : '';

$companyDataAttrs = [
    'encoded-id'   => $rowItem['encoded_id'] ?? '',
    'company-name' => $companyName,
    'email'        => $rowItem['email'] ?? '',
    'phone'        => $rowItem['phone'] ?? '',
    'toll-free'    => $rowItem['toll_free'] ?? '',
    'website'      => $rowItem['website'] ?? '',
    'slogan'       => $rowItem['slogan'] ?? '',
    'address'      => $rowItem['address'] ?? '',
    'city'         => $rowItem['city'] ?? '',
    'country-id'   => $rowItem['country_id'] ?? 0,
    'region-id'    => $rowItem['region_id'] ?? 0,
    'region-name'  => $rowItem['region_name'] ?? 'N/A',
    'country-name' => $rowItem['country_name'] ?? 'N/A',
    'postal-code'  => $rowItem['postal_code'] ?? '',
    'general-summary' => $rowItem['general_summary'] ?? '',
    'joined'       => $rowItem['created_at_formatted'] ?? 'N/A',
    'is-active'    => (int)($rowItem['status_id'] ?? 0) === 1 ? '1' : '0',
    'logo-url'     => $logoUrl,
];

$editClass = 'edit-company-btn';
$deleteClass = 'delete-company-btn';

$statusBadge = (int)($rowItem['status_id'] ?? 0) === 1
    ? '<span class="inline-flex items-center rounded-full bg-primary-50 dark:bg-primary-900/20 px-2.5 py-0.5 text-xs font-bold text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30">Current</span>'
    : '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>';

$initial = strtoupper(substr($companyName, 0, 1));
?>

<tr id="company-row-<?= $rowItem['id'] ?? '0' ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?? '' ?>"
    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-b border-gray-100 dark:border-gray-800 font-sans">

    <td class="px-6 py-4 min-w-0">
        <div class="flex items-start lg:items-center min-w-0">
            <?php if ($hasLogo): ?>
                <img class="h-10 w-10 flex-shrink-0 rounded-full object-cover border border-gray-200 dark:border-gray-700"
                    src="<?= $logoUrl ?>" alt="<?= htmlspecialchars($companyName) ?>">
            <?php else: ?>
                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-lg">
                    <?= htmlspecialchars($initial ?: 'C') ?>
                </div>
            <?php endif; ?>

            <div class="ml-4 flex-1 min-w-0">
                <div class="view-company-trigger cursor-pointer block min-w-0"
                    <?php foreach ($companyDataAttrs as $key => $val): ?>
                    data-<?= $key ?>='<?= htmlspecialchars((string)$val, ENT_QUOTES) ?>'
                    <?php endforeach; ?>>

                    <div class="flex items-center justify-between lg:block">
                        <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors truncate">
                            <?= htmlspecialchars($companyName) ?>
                        </div>
                        <div class="lg:hidden flex-shrink-0 ml-2">
                            <?= $statusBadge ?>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        <?= htmlspecialchars($rowItem['email'] ?? '') ?>
                    </div>
                </div>

                <?php if ($canEdit): ?>
                    <div class="mt-3 lg:hidden flex items-center gap-2">
                        <?php
                        $isMobile = true;
                        $dataAttrs = $companyDataAttrs;
                        $showEdit = true;
                        $showDelete = $isAdmin;
                        include __DIR__ . '/../ui/action-buttons.php';
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
            <?= htmlspecialchars($rowItem['city'] ?? 'N/A') ?>
        </div>
        <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight mt-0.5">
            <?= htmlspecialchars($rowItem['region_name'] ?? 'N/A') ?>, <?= htmlspecialchars($rowItem['country_name'] ?? 'N/A') ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-[11px] text-gray-600 dark:text-gray-400">
            <span class="block font-bold text-gray-400 uppercase text-[9px] tracking-widest text-secondary-400">Phone</span>
            <?= htmlspecialchars($rowItem['phone'] ?: 'N/A') ?>
        </div>
        <div class="text-[11px] text-gray-600 dark:text-gray-400 mt-1 truncate">
            <span class="block font-bold text-gray-400 uppercase text-[9px] tracking-widest text-secondary-400">Website</span>
            <?= htmlspecialchars($rowItem['website'] ?: 'N/A') ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-[11px] text-gray-600 dark:text-gray-400">
            <?= $rowItem['created_at_formatted'] ?? 'N/A' ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <?= $statusBadge ?>
    </td>

    <td class="px-6 py-4 text-right hidden lg:table-cell">
        <?php if ($canEdit): ?>
            <?php
            $isMobile = false;
            $dataAttrs = $companyDataAttrs;
            $showEdit = true;
            $showDelete = $isAdmin;
            include __DIR__ . '/../ui/action-buttons.php';
            ?>
        <?php endif; ?>
    </td>
</tr>
