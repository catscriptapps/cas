<?php
// /resources/views/pdf/photo-library.php
//
// The full Photo Library gallery -- every photo uploaded to this
// inspection, regardless of section/Contracts assignment, printed once
// each right after the last checklist Section. A library photo carries no
// caption of its own (captions live on the section/contract assignment,
// not the library row), so this is a plain grid, no text underneath.

/** @var \App\Models\Inspection $inspection */
/** @var \Illuminate\Support\Collection $pictures InspectionPicture rows */
/** @var string $fullAddress */
?>

<div class="pdf-section-header">
    <div class="pdf-section-title">Photo Library</div>
    <div class="pdf-section-address"><?= htmlspecialchars($fullAddress) ?></div>
</div>

<table style="width:100%; border-collapse:collapse;">
    <?php foreach ($pictures->chunk(2) as $pair): ?>
        <tr>
            <?php foreach ($pair as $picture): ?>
                <?php
                $picPath = realpath(__DIR__ . '/../../../public/images/uploads/inspections/' . $inspection->id . '/' . $picture->filename);
                if (!$picPath) continue;
                ?>
                <td class="pdf-photo-cell">
                    <img src="<?= $picPath ?>" alt="">
                </td>
            <?php endforeach; ?>
            <?php if ($pair->count() === 1): ?><td class="pdf-photo-cell"></td><?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>
