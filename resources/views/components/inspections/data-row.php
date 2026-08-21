<?php
// /resources/views/components/inspections/data-row.php

use App\Models\Inspection;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/** @var Inspection $inspection */
/** @var string $assetBase */

$encodedId = IdEncoder::encode((int)$inspection->id);
$rowColor = $inspection->rowColor();
$statusLabel = Inspection::labelForExpiry($inspection->date_expires?->format('Y-m-d H:i:s'));

$statusBadgeClasses = match ($rowColor) {
    Inspection::COLOR_EXPIRED  => 'bg-red-200 text-red-900',
    Inspection::COLOR_IMMINENT => 'bg-red-300 text-red-950',
    Inspection::COLOR_SOON     => 'bg-yellow-200 text-yellow-900',
    Inspection::COLOR_HEALTHY  => 'bg-green-200 text-green-900',
    default                     => 'bg-gray-100 text-gray-500',
};

$fullAddress = trim(implode(', ', array_filter([
    $inspection->property_address,
    $inspection->city,
    $inspection->region?->region,
])));

$inspectorName = $inspection->inspector?->full_name ?? 'Unknown';
$lastSaved = $inspection->timestamp ? $inspection->timestamp->format('M j, Y') : ($inspection->date_created ? $inspection->date_created->format('M j, Y') : 'N/A');

$canManage = AuthService::isCompanyAdmin() || AuthService::isAdmin() || (int)$inspection->orig_user_id === (int)(AuthService::currentUser()->id ?? 0);
$rowStyle = "background:{$rowColor};color:#000;";
?>

<tr id="inspection-row-<?= (int)$inspection->id ?>" data-encoded-id="<?= $encodedId ?>"
    data-property-address="<?= htmlspecialchars($inspection->property_address) ?>"
    data-city="<?= htmlspecialchars($inspection->city ?? '') ?>"
    data-country-id="<?= (int)($inspection->country_id ?? 0) ?>"
    data-region-id="<?= (int)($inspection->region_id ?? 0) ?>"
    data-postal-code="<?= htmlspecialchars($inspection->postal_code ?? '') ?>"
    data-has-report="<?= $inspection->hasReport() ? '1' : '0' ?>"
    class="transition-colors group border-b border-black/5 font-sans" style="<?= $rowStyle ?>">

    <td class="px-6 py-4 min-w-0">
        <a href="<?= $assetBase ?>inspections/<?= $encodedId ?>" data-partial data-title="<?= htmlspecialchars($inspection->property_address) ?>"
            class="font-bold text-sm hover:underline block truncate">
            <?= htmlspecialchars($inspection->property_address) ?>
        </a>
        <div class="text-xs opacity-70 truncate"><?= htmlspecialchars($fullAddress) ?></div>
    </td>

    <td class="px-6 py-4 hidden md:table-cell">
        <div class="text-sm font-medium"><?= htmlspecialchars($inspection->city ?? 'N/A') ?></div>
        <div class="text-[10px] font-bold opacity-60 uppercase tracking-tight mt-0.5"><?= htmlspecialchars($inspection->region?->region ?? 'N/A') ?></div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell text-sm font-medium"><?= htmlspecialchars($inspectorName) ?></td>

    <td class="px-6 py-4 hidden sm:table-cell">
        <div class="flex items-center gap-1">
            <span class="text-xs font-mono font-bold"><?= htmlspecialchars($inspection->access_code ?? 'N/A') ?></span>
            <?php if (!empty($inspection->access_code)): ?>
                <button type="button" data-action="copy-access-code" data-code="<?= htmlspecialchars($inspection->access_code, ENT_QUOTES) ?>" title="Copy access code"
                    class="p-1 rounded text-current opacity-60 hover:opacity-100 hover:bg-black/10 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            <?php endif; ?>
        </div>
        <span class="inline-flex items-center mt-1 rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wide <?= $statusBadgeClasses ?>">
            <?= htmlspecialchars($statusLabel) ?>
        </span>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell text-xs">
        <?= $inspection->date_expires ? htmlspecialchars($inspection->date_expires->format('M j, Y')) : '—' ?>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell text-xs"><?= htmlspecialchars($lastSaved) ?></td>

    <td class="px-6 py-4 text-right">
        <?php if ($canManage): ?>
            <div class="flex items-center justify-end gap-1">
                <button type="button" data-action="edit-inspection" title="Edit Header"
                    data-encoded-id="<?= $encodedId ?>"
                    data-property-address="<?= htmlspecialchars($inspection->property_address, ENT_QUOTES) ?>"
                    data-city="<?= htmlspecialchars($inspection->city ?? '', ENT_QUOTES) ?>"
                    data-country-id="<?= (int)($inspection->country_id ?? 0) ?>"
                    data-region-id="<?= (int)($inspection->region_id ?? 0) ?>"
                    data-postal-code="<?= htmlspecialchars($inspection->postal_code ?? '', ENT_QUOTES) ?>"
                    class="edit-inspection-btn p-1.5 rounded-md text-current opacity-60 hover:opacity-100 hover:bg-black/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button type="button" data-action="delete-inspection" data-id="<?= $encodedId ?>" title="Delete Inspection"
                    class="delete-inspection-btn p-1.5 rounded-md text-current opacity-60 hover:opacity-100 hover:bg-red-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </td>
</tr>
