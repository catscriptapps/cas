<?php
// /resources/views/pdf/contracts.php
//
// Contract Documents page -- photos assigned via the Photo Library's
// "Contract" checkbox, printed right before Section 1. Same photo-grid
// treatment as a section's own photos (see section.php), just with no
// question checklist above it.

/** @var \App\Models\Inspection $inspection */
/** @var \Illuminate\Support\Collection $links InspectionPictureContract rows, picture eager-loaded */
/** @var string $fullAddress */
?>

<div class="pdf-section-header">
    <div class="pdf-section-title">Contract Documents</div>
    <div class="pdf-section-address"><?= htmlspecialchars($fullAddress) ?></div>
</div>

<table style="width:100%; border-collapse:collapse;">
    <?php foreach ($links->chunk(2) as $pair): ?>
        <tr>
            <?php foreach ($pair as $link): ?>
                <?php
                $picPath = realpath(__DIR__ . '/../../../public/images/uploads/inspections/' . $inspection->id . '/' . $link->picture->filename);
                if (!$picPath) continue;
                ?>
                <td class="pdf-photo-cell">
                    <img src="<?= $picPath ?>" alt="">
                    <?php if (trim((string)$link->description) !== ''): ?>
                        <div class="pdf-photo-caption"><?= htmlspecialchars($link->description) ?></div>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
            <?php if ($pair->count() === 1): ?><td class="pdf-photo-cell"></td><?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>
