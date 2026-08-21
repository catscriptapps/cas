// /resources/js/components/data-table.js
//
// Generic, reusable engine for "big list" table pages: sticky header,
// per-column server-side text filters, sortable columns, and filter-safe
// infinite scroll, with a footer count kept in sync against the server's
// authoritative total (not just what's currently rendered). Meant to be the
// standard going forward for every records table in the app -- Users and
// Inspections were the first two pages wired to it (filters only); History
// was the first to also use sortable columns.
//
// Sort state is client-side only (a `[data-sort-column]` button's dataset
// value, toggled asc/desc on repeat clicks) -- the server contract only
// needs to read it back off the query string, there's nothing to persist.
//
// Server contract (GET {endpoint}?filter[col]=val&sort=col&dir=asc|desc&page=N&...extraParams):
//   { success: true, data: [{ rowHtml }], meta: { total, hasMore } }
//
// Extensibility for a future totals row: pass `onRowsRendered(rows, meta,
// { isAppend })` and accumulate whatever a page needs (e.g. summing a
// numeric column) into its own <tfoot> -- this module doesn't render totals
// itself since neither Users nor Inspections have a numeric column to total
// today, but every render pass (initial load, filter, and each infinite-
// scroll page) already flows through one place so a future page can hook in
// without touching this file.

const DEBOUNCE_MS = 350;

export function initDataTable({
    tbodyId,
    countId,
    endpoint,
    resourceLabel,
    filterInputSelector = '[data-filter-column]',
    sortButtonSelector = '[data-sort-column]',
    rowSelector = 'tr[data-encoded-id]',
    defaultSort = null,
    defaultDir = 'asc',
    extraParams = {},
    perPage = 50,
    colspan = 6,
    emptyMessage = null,
    onRowsRendered = null,
}) {
    const tbody = document.getElementById(tbodyId);
    const countEl = countId ? document.getElementById(countId) : null;
    if (!tbody) return null;

    const table = tbody.closest('table');
    const filterInputs = table ? Array.from(table.querySelectorAll(filterInputSelector)) : [];
    const sortButtons = table ? Array.from(table.querySelectorAll(sortButtonSelector)) : [];

    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let debounceTimer = null;
    let fetchController = null;
    let currentSort = defaultSort;
    let currentDir = defaultDir === 'desc' ? 'desc' : 'asc';

    // The server already renders page 1 into the HTML on a plain page load;
    // its total (via tbody[data-total]) lets the footer count and hasMore
    // initialize immediately with no redundant AJAX round-trip.
    const initialRowCount = tbody.querySelectorAll(`:scope > ${rowSelector}`).length;
    const initialTotal = tbody.dataset.total !== undefined ? parseInt(tbody.dataset.total, 10) : NaN;
    if (!Number.isNaN(initialTotal)) {
        hasMore = initialRowCount < initialTotal;
    }

    const irregularPlurals = { property: 'properties', company: 'companies' };
    const pluralize = (label) => irregularPlurals[label] || `${label}s`;

    function currentFilters() {
        const filters = {};
        filterInputs.forEach((input) => {
            const value = input.value.trim();
            if (value !== '') filters[input.dataset.filterColumn] = value;
        });
        return filters;
    }

    function buildUrl(page) {
        const params = new URLSearchParams({ page: String(page), ...extraParams });
        const filters = currentFilters();
        Object.entries(filters).forEach(([col, val]) => params.set(`filter[${col}]`, val));
        if (currentSort) {
            params.set('sort', currentSort);
            params.set('dir', currentDir);
        }
        return `${endpoint}?${params.toString()}`;
    }

    function updateSortIndicators() {
        sortButtons.forEach((btn) => {
            const isActive = btn.dataset.sortColumn === currentSort;
            btn.classList.toggle('text-primary-600', isActive);
            btn.classList.toggle('dark:text-primary-400', isActive);
            const icon = btn.querySelector('[data-sort-icon]');
            if (!icon) return;
            icon.classList.toggle('opacity-100', isActive);
            icon.classList.toggle('opacity-30', !isActive);
            icon.classList.toggle('rotate-180', isActive && currentDir === 'asc');
        });
    }

    function updateCount(loadedCount, total) {
        if (!countEl) return;
        const label = resourceLabel.toLowerCase();
        const labelPlural = pluralize(label);
        const finalTotal = typeof total === 'number' ? total : loadedCount;

        countEl.textContent = loadedCount >= finalTotal
            ? `Showing ${finalTotal} ${finalTotal === 1 ? label : labelPlural}`
            : `Showing ${loadedCount} of ${finalTotal} ${finalTotal === 1 ? label : labelPlural}`;
    }

    function renderEmptyState() {
        const message = emptyMessage || `No ${pluralize(resourceLabel.toLowerCase())} found.`;
        tbody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center">
                        <p class="font-medium font-sans">${message}</p>
                    </div>
                </td>
            </tr>
        `;
    }

    async function fetchPage(page, { append }) {
        if (isLoading) return;
        isLoading = true;

        if (fetchController) fetchController.abort();
        fetchController = new AbortController();

        try {
            const response = await fetch(buildUrl(page), {
                signal: fetchController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json();
            if (!result.success) return;

            const rows = result.data || [];
            const meta = result.meta || { total: rows.length, hasMore: false };

            if (append) {
                if (rows.length > 0) {
                    tbody.insertAdjacentHTML('beforeend', rows.map((r) => r.rowHtml).join(''));
                }
            } else if (rows.length === 0) {
                renderEmptyState();
            } else {
                tbody.innerHTML = rows.map((r) => r.rowHtml).join('');
            }

            hasMore = !!meta.hasMore;
            currentPage = page;

            const loadedCount = tbody.querySelectorAll(`:scope > ${rowSelector}`).length;
            updateCount(loadedCount, meta.total);

            if (typeof onRowsRendered === 'function') {
                onRowsRendered(rows, meta, { isAppend: append });
            }
        } catch (err) {
            if (err.name !== 'AbortError') console.error('Data table fetch error:', err);
        } finally {
            isLoading = false;
        }
    }

    function applyFilters() {
        hasMore = true;
        fetchPage(1, { append: false });
    }

    function scheduleFilter() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, DEBOUNCE_MS);
    }

    filterInputs.forEach((input) => {
        input.addEventListener('input', scheduleFilter);
    });

    sortButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const column = btn.dataset.sortColumn;
            if (currentSort === column) {
                currentDir = currentDir === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = column;
                currentDir = 'asc';
            }
            updateSortIndicators();
            applyFilters();
        });
    });
    updateSortIndicators();

    if (!Number.isNaN(initialTotal)) {
        updateCount(initialRowCount, initialTotal);
    }

    // Infinite scroll -- IntersectionObserver on a sentinel appended right
    // after the table, so the browser (not a scroll listener firing on
    // every pixel) decides when to fetch the next page. Filters are never
    // touched here; buildUrl() always reads whatever's currently in the
    // filter inputs, so scrolling further just requests the next page of
    // the SAME filtered result set.
    const sentinel = document.createElement('div');
    sentinel.setAttribute('aria-hidden', 'true');
    sentinel.className = 'h-px';
    table?.insertAdjacentElement('afterend', sentinel);

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMore && !isLoading) {
            fetchPage(currentPage + 1, { append: true });
        }
    }, { rootMargin: '600px 0px' });
    observer.observe(sentinel);

    return {
        /** Layer a static extra param (e.g. a status dropdown) on top of the current filters. */
        setExtraParams(newParams) {
            extraParams = newParams || {};
            applyFilters();
        },
        refresh: applyFilters,
    };
}
