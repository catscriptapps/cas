<?php
// /resources/views/pdf/summary.php

/** @var \App\Models\Inspection $inspection */
?>

<h2 class="pdf-summary-heading">Inspection Summary</h2>
<div class="pdf-summary-body"><?= nl2br(htmlspecialchars($inspection->inspection_summary)) ?></div>
