<?php
// /resources/views/components/ui/table-filter-input.php

/**
 * A single per-column text filter input for a sticky data-table header,
 * driven by resources/js/components/data-table.js.
 *
 * @var string $filterColumn The filter key (matches a `filter[key]` the
 *   controller's index() reads server-side).
 * @var string $filterPlaceholder
 * @var string|null $filterInputType 'text' (default) or 'search' -- 'search'
 *   gets the browser's native clickable "x" clear button, used by
 *   client-side-filtered tables (e.g. the Schedules games table) where
 *   clearing via that x needs to fire a plain `input` event, which it does.
 */
$filterInputType = $filterInputType ?? 'text';
?>
<input type="<?= htmlspecialchars($filterInputType) ?>" data-filter-column="<?= htmlspecialchars($filterColumn) ?>" placeholder="<?= htmlspecialchars($filterPlaceholder ?? 'Filter…') ?>"
    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-xs font-normal normal-case tracking-normal py-1.5 px-2.5 placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400 transition-colors">
