<?php
// /resources/views/pdf/general-summary.php

/** @var \App\Models\Company $company */
?>

<h2 class="pdf-summary-heading">What to Expect from your Inspection</h2>
<div class="pdf-summary-body"><?= nl2br(htmlspecialchars($company->general_summary)) ?></div>
