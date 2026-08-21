<?php
// /resources/views/pages/standards.php

declare(strict_types=1);

use Src\Service\AuthService;

if (!AuthService::currentUser() || empty(AuthService::currentUser()->company_id)) {
    include __DIR__ . '/auth-required.php';
    return;
}

$controller = new \Src\Controller\StandardsController();
$controller->index();

$standardsRows = $GLOBALS['standardsRows'] ?? '';
$standardsCount = $GLOBALS['standardsCount'] ?? 0;
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Standards' => '/standards'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Standards</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Manage your Standards of Practice pages. Every page here prints verbatim, in order, on every finished PDF report your company generates.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <button type="button" id="add-standard-page-btn" data-tooltip="Add Page"
                class="shrink-0 flex items-center justify-center rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden xs:inline md:inline">Add Page</span>
            </button>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <span id="standards-count" class="text-[11px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-950/30 px-2 py-0.5 rounded-md border border-primary-100 dark:border-primary-900/50">
            <?= (int)$standardsCount ?> page<?= (int)$standardsCount === 1 ? '' : 's' ?>
        </span>
        <span class="text-[11px] text-gray-400 dark:text-gray-500 font-medium">Click a page's move icon, then click another page to place it there</span>
    </div>

    <div id="standards-list" class="space-y-4">
        <?php if (empty($standardsRows)): ?>
            <div class="empty-state-placeholder py-12 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl bg-white dark:bg-gray-900">
                <p class="text-gray-400 text-sm italic">There are currently no Standards of Practice pages for your company. Use "Add Page" to create one.</p>
            </div>
        <?php else: ?>
            <?= $standardsRows ?>
        <?php endif; ?>
    </div>
</div>
