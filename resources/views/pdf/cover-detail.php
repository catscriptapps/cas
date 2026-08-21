<?php
// /resources/views/pdf/cover-detail.php
//
// The auto-generated title page that always appears (mirrors legacy's
// PDFCoverPages::detail()) -- company branding, property address, and the
// inspection date. Sits between the company's uploaded Front cover images
// and the Standards of Practice pages.

/** @var \App\Models\Company $company */
/** @var \App\Models\Inspection $inspection */
/** @var string $fullAddress */

$logoPath = null;
if (!empty($company->logo_url)) {
    $resolved = realpath(__DIR__ . '/../../../public/images/uploads/company-logos/' . $company->logo_url);
    if ($resolved) $logoPath = $resolved;
}

$inspectionDate = $inspection->timestamp
    ? $inspection->timestamp->format('F j, Y')
    : ($inspection->date_created ? $inspection->date_created->format('F j, Y') : date('F j, Y'));

$contactParts = array_filter([
    $company->phone,
    $company->email,
    $company->website,
]);
?>

<div class="pdf-title-page">
    <?php if ($logoPath): ?>
        <img src="<?= $logoPath ?>" class="pdf-title-logo" alt="">
    <?php endif; ?>

    <div class="pdf-title-company"><?= htmlspecialchars($company->company_name) ?></div>
    <div class="pdf-title-report-label">Certified Home Inspection Report</div>

    <div class="pdf-title-address"><?= htmlspecialchars($inspection->property_address) ?></div>
    <div class="pdf-title-subaddress"><?= htmlspecialchars($fullAddress) ?></div>

    <div class="pdf-title-date">Inspection Date: <?= htmlspecialchars($inspectionDate) ?></div>

    <?php if (!empty($contactParts)): ?>
        <div class="pdf-title-footer"><?= implode('  &middot;  ', array_map('htmlspecialchars', $contactParts)) ?></div>
    <?php endif; ?>
</div>
