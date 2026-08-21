<?php
// /resources/views/components/ui/faq-actions.php

declare(strict_types=1);

/**
 * @var array  $dataAttrs  The data attributes from the FAQ model
 * @var bool   $isMobile   Whether this is for the bottom of the card (mobile)
 */

$dataString = '';
foreach ($dataAttrs as $key => $value) {
    $dataString .= ' data-' . htmlspecialchars($key) . '="' . htmlspecialchars((string)$value) . '"';
}

// Re-using your logic for classes
$editClass = 'edit-faq-btn';
$deleteClass = 'delete-faq-btn';
?>

<div class="<?= $isMobile ? 'flex items-center gap-4' : 'flex items-center gap-2' ?>">
    <button type="button"
        class="<?= $editClass ?> p-2 rounded-lg text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors flex items-center gap-1.5 font-bold text-xs"
        <?= $dataString ?>>
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        <?= $isMobile ? 'Edit Question' : '' ?>
    </button>

    <button type="button"
        class="<?= $deleteClass ?> p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex items-center gap-1.5 font-bold text-xs"
        <?= $dataString ?>>
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <?= $isMobile ? 'Delete' : '' ?>
    </button>
</div>