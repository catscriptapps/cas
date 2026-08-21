<?php
// /resources/views/pdf/section-footer.php
//
// Ports legacy's pdf_body.php footer() intact: a 3-row-of-5-cell legend
// (status glyphs + Tasks glyphs + Date/Inspector Initials), a spacer row,
// and a copyright row -- printed once per section, right after that
// section's questions/conditions and before its photo grid, same position
// legacy uses.

/** @var \App\Models\Inspection $inspection */

$dateLabel = $inspection->date_created ? $inspection->date_created->format('F j, Y') : '';
$initials = htmlspecialchars($inspection->initials ?? '');
?>
<table class="pdf-footer-table">
    <tr>
        <td class="pdf-footer-icon-cell pdf-footer-corner">&nbsp;</td>
        <td class="pdf-footer-corner">&nbsp;</td>
        <td class="pdf-footer-icon-cell">&#10004;</td>
        <td>Inspected</td>
        <td>Date: <?= htmlspecialchars($dateLabel) ?></td>
    </tr>
    <tr>
        <td class="pdf-footer-icon-cell">&#9830;</td>
        <td>Observe and Report</td>
        <td class="pdf-footer-icon-cell">x</td>
        <td>Not Inspected</td>
        <td>Inspector Initials:</td>
    </tr>
    <tr>
        <td class="pdf-footer-icon-cell">&#9632;</td>
        <td>Perform Tasks</td>
        <td class="pdf-footer-icon-cell">n&frasl;a</td>
        <td>Not Applicable</td>
        <td><?= $initials !== '' ? $initials : '&mdash;' ?></td>
    </tr>
    <tr><td colspan="5" class="pdf-footer-spacer">&nbsp;</td></tr>
    <tr><td colspan="5" class="pdf-footer-copyright">&copy; <?= date('Y') ?> Home Comfort Reports</td></tr>
</table>
