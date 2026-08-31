<?php
// /resources/views/pages/suspension-matrix.php

declare(strict_types=1);

/**
 * Guest-facing "Suspension Matrix" page under the League Details dropdown.
 * Legacy links straight to a PDF (essa_suspension_matrix_2022_23.pdf) that
 * doesn't exist anywhere in either legacy checkout -- placeholder page until
 * the real document is available; swap this file's content for a direct PDF
 * link (matching the two Rulebook entries) once it is.
 */
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php
    $breadcrumbs = ['League Details' => '/league-details', 'Suspension Matrix' => '/suspension-matrix'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="p-10 sm:p-12 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm text-center">
        <div class="h-16 w-16 mx-auto rounded-2xl bg-secondary-100 dark:bg-secondary-900/30 text-secondary-700 dark:text-secondary-400 flex items-center justify-center mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>

        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-secondary-100 dark:bg-secondary-900/30 text-secondary-700 dark:text-secondary-400 mb-4">
            Coming Soon
        </span>

        <?php
        // No title here -- the shared hero above the topbar already shows
        // "Suspension Matrix" for every viewer, admin included (see
        // layout-header.php).
        ?>
        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium max-w-md mx-auto leading-relaxed">
            Our discipline and suspension reference guide is being finalized for this season and will be posted here shortly.
            For a specific ruling, reach out through the Contact page.
        </p>

        <a href="<?= $baseUrl ?>contact" data-partial
            class="inline-flex items-center gap-2 mt-8 px-8 py-3 rounded-full bg-primary-400 hover:bg-secondary-400 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
            Contact Us
        </a>
    </div>
</div>
