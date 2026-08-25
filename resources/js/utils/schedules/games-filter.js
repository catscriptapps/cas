// /resources/js/utils/schedules/games-filter.js
//
// Client-side, per-table text filtering for the Regular Season / Playoffs
// games tables (see components/schedules/games-table.php). Unlike the rest
// of the app's tables, this doesn't hit the server -- the whole season's
// games are already in the DOM (a season is at most a couple hundred games),
// so filtering is just show/hide against each row's `data-filter-*`
// attributes.
//
// The one wrinkle: normally a date is only printed on the first row of that
// date (the rest of that day's games leave the Date cell blank, relying on
// visual grouping). Once a filter is active that grouping stops making
// sense -- the visible rows are no longer a contiguous run per day -- so
// every visible row shows its own date, and the "spacer" rows between date
// groups are hidden. Clearing every filter restores the original
// grouped-with-blanks rendering exactly as the server produced it.

const DEBOUNCE_MS = 200;

function toFilterKey(column) {
    return 'filter' + column.charAt(0).toUpperCase() + column.slice(1);
}

function applyFilters(table) {
    const filterInputs = table.querySelectorAll('[data-filter-column]');
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const filters = [];
    filterInputs.forEach((input) => {
        const value = input.value.trim().toLowerCase();
        if (value) filters.push([toFilterKey(input.dataset.filterColumn), value]);
    });
    const isFiltering = filters.length > 0;

    let visibleCount = 0;

    Array.from(tbody.children).forEach((row) => {
        if (row.classList.contains('date-separator-row')) {
            row.classList.toggle('hidden', isFiltering);
            return;
        }
        if (row.classList.contains('no-filter-results-row')) return;

        const matches = filters.every(([key, needle]) => (row.dataset[key] || '').toLowerCase().includes(needle));
        row.classList.toggle('hidden', !matches);
        if (matches) visibleCount++;

        const dateCell = row.querySelector('.game-date-cell');
        if (dateCell) {
            dateCell.classList.toggle('hidden', isFiltering ? false : dateCell.dataset.dateShown !== '1');
        }
    });

    const emptyRow = tbody.querySelector('.no-filter-results-row');
    if (emptyRow) emptyRow.classList.toggle('hidden', !isFiltering || visibleCount > 0);
}

function initTable(table) {
    if (table._gamesFilterInitialized) return;
    table._gamesFilterInitialized = true;

    const filterInputs = table.querySelectorAll('[data-filter-column]');
    if (!filterInputs.length) return;

    let debounceTimer;
    filterInputs.forEach((input) => {
        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => applyFilters(table), DEBOUNCE_MS);
        });
    });
}

export function initGamesFilters() {
    document.querySelectorAll('.games-filterable-table').forEach(initTable);
}
