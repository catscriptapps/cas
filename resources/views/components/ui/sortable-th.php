<?php
// /resources/views/components/ui/sortable-th.php
//
// A clickable column-header label for a sticky data-table header, driven by
// resources/js/components/data-table.js. The chevron dims when this column
// isn't the active sort and flips direction (asc = pointing up) when it is.
//
// @var string $sortColumn The sort key (matches a `sort=` value the
//   controller's index() reads server-side).
// @var string $sortLabel
?>
<button type="button" data-sort-column="<?= htmlspecialchars($sortColumn) ?>"
    class="inline-flex items-center gap-1 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
    <span><?= htmlspecialchars($sortLabel) ?></span>
    <svg data-sort-icon class="w-3 h-3 opacity-30 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
    </svg>
</button>
