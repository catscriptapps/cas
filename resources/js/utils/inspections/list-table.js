// /resources/js/utils/inspections/list-table.js

import { initDataTable } from '../../components/data-table.js';

/**
 * Wires the Inspections list page onto the shared sticky-header /
 * per-column-filter / infinite-scroll data table, layering the Current/
 * Archived dropdown on top as a static extra param so switching it re-runs
 * the same fetch (page 1, current filters intact) with the new view.
 */
export function initInspectionsListTable() {
    const table = initDataTable({
        tbodyId: 'inspections-tbody',
        countId: 'inspections-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/inspections`,
        resourceLabel: 'inspection',
        colspan: 7,
        defaultSort: 'last_saved',
        defaultDir: 'desc',
        extraParams: { view: 'current' },
    });
    if (!table) return;

    const viewSelect = document.getElementById('inspections-view-select');
    if (viewSelect) {
        viewSelect.addEventListener('change', () => {
            table.setExtraParams({ view: viewSelect.value });
        });
    }
}
