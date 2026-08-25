<?php
// /resources/views/components/incident-reports/data-row.php

use Src\Service\AuthService;

/** @var array $rowItem */

$canManage = AuthService::isLoggedIn();

$rawDate = $rowItem['incident_date'] ? date('Y-m-d', strtotime($rowItem['incident_date'])) : '';
$statusLabel = (int)$rowItem['status_id'] === 1 ? 'Filed' : 'Draft';

$incidentDataAttrs = [
    'encoded-id' => $rowItem['encoded_id'],
    'incident-date' => $rawDate,
    'incident-time' => $rowItem['incident_time'] ?? '',
    'teams-involved' => $rowItem['teams_involved'] ?? '',
    'persons-involved' => $rowItem['persons_involved'] ?? '',
    'location' => $rowItem['location'] ?? '',
    'ref-involved' => $rowItem['ref_involved'] ?? '',
    'timekeeper' => $rowItem['timekeeper'] ?? '',
    'incident' => $rowItem['incident'] ?? '',
    'equipment-worn' => $rowItem['equipment_worn'] ?? '',
    'medical-assistance' => $rowItem['medical_assistance'] ?? '',
    'manager-name' => $rowItem['manager_name'] ?? '',
    'manager-time' => $rowItem['manager_time'] ?? '',
    'referee-outcome' => $rowItem['referee_outcome'] ?? '',
    'signature' => $rowItem['name_e_signature'] ?? '',
    'is-active' => (int)$rowItem['status_id'] === 1 ? '1' : '0',
    'status' => $statusLabel,
    'date-formatted' => $rowItem['date_formatted'] ?? '',
];

$editClass = 'edit-report-btn';
$deleteClass = 'delete-report-btn';

$ts = $rowItem['incident_date'] ? strtotime($rowItem['incident_date']) : null;
$month = $ts ? date('M', $ts) : '???';
$day = $ts ? date('j', $ts) : '?';
$year = $ts ? date('Y', $ts) : '';

$dataAttrString = '';
foreach ($incidentDataAttrs as $key => $val) {
    $dataAttrString .= ' data-' . $key . '="' . htmlspecialchars((string)$val) . '"';
}
?>
<tr id="report-row-<?= $rowItem['entry_id'] ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?>"
    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-b border-gray-100 dark:border-gray-800 font-sans">

    <td class="px-6 py-4 min-w-0">
        <div class="flex items-start lg:items-center min-w-0">
            <div class="h-12 w-12 py-1 flex-shrink-0 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold flex-col leading-none border border-primary-200 dark:border-primary-800/50">
                <span class="text-[10px] uppercase tracking-tighter mb-0.5"><?= $month ?></span>
                <span class="text-lg"><?= $day ?></span>
            </div>

            <div class="ml-4 flex-1 min-w-0 overflow-hidden">
                <div class="view-report-trigger cursor-pointer group/title block min-w-0" <?= $dataAttrString ?>>
                    <div class="text-sm font-bold text-gray-900 dark:text-white group-hover/title:text-primary-600 transition-colors truncate">
                        <span class="lg:hidden"><?= htmlspecialchars($rowItem['location'] ?: 'Unknown Location') ?></span>
                        <span class="hidden lg:inline"><?= $year ?></span>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        <span class="lg:hidden"><?= $year ?> &bull; <?= htmlspecialchars($rowItem['incident_time'] ?: 'No Time') ?></span>
                        <span class="hidden lg:inline"><?= htmlspecialchars($rowItem['incident_time'] ?: 'No Time Set') ?></span>
                    </div>

                    <div class="lg:hidden mt-1.5 flex flex-wrap items-center gap-2">
                        <span class="text-[11px] text-gray-500 truncate max-w-[120px]"><?= htmlspecialchars((string)$rowItem['teams_involved']) ?></span>
                        <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                        <span class="text-[10px] font-bold uppercase whitespace-nowrap <?= (int)$rowItem['status_id'] === 1 ? 'text-secondary-600' : 'text-gray-400' ?>">
                            <?= $statusLabel ?>
                        </span>
                    </div>
                </div>

                <?php if ($canManage): ?>
                    <?php
                    $dataAttrs = $incidentDataAttrs;
                    $isMobile = true;
                    include __DIR__ . '/../ui/action-buttons.php';
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell min-w-0">
        <div class="view-report-trigger cursor-pointer text-sm font-medium text-gray-900 dark:text-gray-100 truncate hover:text-primary-600 transition-colors" <?= $dataAttrString ?>>
            <?= htmlspecialchars($rowItem['location'] ?: 'Unknown Location') ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell min-w-0">
        <div class="view-report-trigger cursor-pointer hover:bg-gray-100/50 dark:hover:bg-gray-700/30 rounded-lg p-1 -m-1 transition-all" <?= $dataAttrString ?>>
            <div class="text-sm text-gray-900 dark:text-gray-100 truncate" title="<?= htmlspecialchars((string)$rowItem['teams_involved']) ?>">
                <?= htmlspecialchars((string)$rowItem['teams_involved']) ?>
            </div>
            <div class="text-xs text-gray-400 italic">
                Ref: <?= htmlspecialchars($rowItem['ref_involved'] ?: 'N/A') ?>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold border
            <?= (int)$rowItem['status_id'] === 1
                ? 'bg-secondary-50 text-secondary-700 border-secondary-100 dark:bg-secondary-900/30 dark:text-secondary-400 dark:border-secondary-800'
                : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' ?>">
            <?= $statusLabel ?>
        </span>
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-right hidden lg:table-cell">
        <?php if ($canManage): ?>
            <?php
            $dataAttrs = $incidentDataAttrs;
            $isMobile = false;
            include __DIR__ . '/../ui/action-buttons.php';
            ?>
        <?php endif; ?>
    </td>
</tr>
