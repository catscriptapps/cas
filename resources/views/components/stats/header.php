<?php
// /resources/views/components/stats/header.php

$divisionName = $divisionName ?? 'Unknown';
$seasonYear = $seasonYear ?? '';
?>

<?php
// Sticky under the fixed topbar -- same offset/overflow-clip pattern proven
// on the Schedules detail page (see components/schedules/header.php).
?>
<div class="sticky top-[82px] sm:top-[98px] z-[35] bg-gray-50 dark:bg-slate-900 pt-4 pb-4 -mt-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 w-full">
        <div class="min-w-0">
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    <li><a href="<?= $baseUrl ?>stats" data-partial class="hover:text-primary-600 transition-colors">Stats+Standings</a></li>
                    <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                        </svg></li>
                    <li class="text-gray-900 dark:text-white">Stats Detail</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white font-sans tracking-tight uppercase truncate">
                Stats for <span class="text-primary-600"><?= htmlspecialchars((string)$divisionName) ?></span>
                <span class="text-gray-400 font-light ml-1"><?= htmlspecialchars((string)$seasonYear) ?></span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button id="export-pdf-btn" title="Print PDF" class="flex items-center gap-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-4 py-2 rounded-xl text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                PDF
            </button>

            <a id="back-to-stats-btn"
                data-tooltip="Go Back"
                href="<?= $baseUrl ?>stats"
                data-partial
                class="flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-xl text-xs font-bold transition-all
              dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </div>

    <div class="flex items-center gap-2 mt-5 bg-gray-100 dark:bg-slate-800/60 p-1 rounded-xl w-fit">
        <button type="button" id="tab-regular" class="stats-tab-btn px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition-all bg-primary-500 text-white shadow-sm">
            Regular Season
        </button>
        <button type="button" id="tab-playoffs" class="stats-tab-btn px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition-all bg-transparent text-gray-500 dark:text-gray-400">
            Playoffs
        </button>
    </div>
</div>
