<?php
// /resources/views/components/schedules/header.php

use Src\Service\AuthService;
?>

<?php
// Sticky under the fixed topbar (see layouts/app.php's #app-shell-content
// offset for the topbar's true rendered height, 82px/98px -- this sticks
// flush beneath it) so the breadcrumb/title/actions stay visible while
// scrolling through a long game list, matching the sticky table headers
// below it. Needs its own opaque background (matching <body>'s) since it
// slides over page content once stuck.
?>
<div class="sticky top-[82px] sm:top-[98px] z-[35] bg-gray-50 dark:bg-slate-900 pt-4 pb-4 -mt-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 w-full">
        <div class="min-w-0">
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    <li><a href="<?= $baseUrl ?>schedules" data-partial class="hover:text-primary-600 transition-colors">Schedules</a></li>
                    <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                        </svg></li>
                    <li class="text-gray-900 dark:text-white">Schedule Detail</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white font-sans tracking-tight uppercase truncate">
                Schedule for <span class="text-primary-600"><?= htmlspecialchars((string)$divisionName) ?></span>
                <span class="text-gray-400 font-light ml-1"><?= htmlspecialchars((string)$seasonYear) ?></span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <?php if (AuthService::isAdmin()): ?>
                <button title="Add new schedule" id="add-schedule-btn" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    ADD
                </button>
            <?php endif; ?>

            <button id="view-all-schedules-btn"
                title="View all schedules"
                class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-xs font-bold transition-all
                   dark:bg-slate-700/50 dark:hover:bg-slate-700 dark:text-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                VIEW ALL
            </button>

            <button id="export-pdf-btn" title="Print PDF" class="flex items-center gap-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-4 py-2 rounded-xl text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                PDF
            </button>

            <a id="back-to-schedules-btn"
                data-tooltip="Go Back"
                href="<?= $baseUrl ?>schedules"
                data-partial
                class="flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-xl text-xs font-bold transition-all
              dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </div>
</div>
