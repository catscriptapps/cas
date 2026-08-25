// /resources/js/utils/gamesheets/accordion-handler.js

import { handleAutoSave } from './save-handler.js';
import { showSpinner, hideSpinner } from '../../ui/spinner.js';

/**
 * Clicking a game row fetches its home/away roster stat tables and injects
 * them as a sibling `<tr class="detail-row">` directly below the trigger
 * row (an in-place accordion, not a route change -- unlike Schedules, there
 * is no per-game detail page).
 */
export function initGamesheetAccordion() {
    const container = document.querySelector('#schedule-content-area');
    if (!container) return;

    container.addEventListener('change', handleAutoSave);

    container.addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-game-id]');
        if (row) handleRowClick(e);
    });
}

async function handleRowClick(e) {
    const row = e.target.closest('tr[data-game-id]');
    if (!row) return;

    // Don't trigger when clicking something interactive inside the row.
    if (e.target.closest('button, a, input')) return;

    const encodedGameId = row.getAttribute('data-game-id');
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    const detailRow = row.nextElementSibling;
    if (detailRow && detailRow.classList.contains('detail-row')) {
        detailRow.classList.toggle('hidden');
        return;
    }

    showSpinner();
    try {
        const response = await fetch(`${baseUrl}api/gamesheets?action=get_rosters&game_id=${encodeURIComponent(encodedGameId)}`);
        const result = await response.json();

        if (result.success) {
            const newRow = document.createElement('tr');
            newRow.className = 'detail-row border-b border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-900/30';
            newRow.innerHTML = `<td colspan="6" class="p-0">${result.html}</td>`;

            row.after(newRow);
            initRosterSorting(newRow);
        }
    } catch (err) {
        console.error('Failed to load rosters:', err);
    } finally {
        hideSpinner();
    }
}

/**
 * Pure client-side column sort for one roster table -- no server round-trip,
 * no persisted preference.
 */
function initRosterSorting(container) {
    const headers = container.querySelectorAll('th[data-sort]');

    headers.forEach((header) => {
        header.addEventListener('click', () => {
            const table = header.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const type = header.getAttribute('data-sort');
            const isAscending = header.classList.contains('sort-asc');

            table.querySelectorAll('th').forEach((h) => h.classList.remove('sort-asc', 'sort-desc', 'text-primary-600'));

            const sortedRows = rows.sort((a, b) => {
                let valA, valB;

                if (type === 'number') {
                    valA = a.querySelector('td:first-child').innerText.trim();
                    valB = b.querySelector('td:first-child').innerText.trim();
                    if (valA === 'G') return isAscending ? 1 : -1;
                    if (valB === 'G') return isAscending ? -1 : 1;
                    return isAscending ? parseInt(valB, 10) - parseInt(valA, 10) : parseInt(valA, 10) - parseInt(valB, 10);
                }

                valA = a.querySelector('td:nth-child(2)').innerText.trim().toLowerCase();
                valB = b.querySelector('td:nth-child(2)').innerText.trim().toLowerCase();
                return isAscending ? valB.localeCompare(valA) : valA.localeCompare(valB);
            });

            header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
            header.classList.add('text-primary-600');

            tbody.append(...sortedRows);
        });
    });
}
