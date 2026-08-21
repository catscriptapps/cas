// /resources/js/utils/history/list-table.js

import { initDataTable } from '../../components/data-table.js';

/**
 * Wires the History page onto the shared sticky-header / per-column-filter /
 * sortable-column / infinite-scroll data table. History rows carry a plain
 * `data-id` (the raw recent_activities.id), not an IdEncoder-encoded id like
 * every other table this engine drives, hence the custom rowSelector.
 */
export function initHistoryListTable() {
    return initDataTable({
        tbodyId: 'history-tbody',
        countId: 'history-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/history`,
        resourceLabel: 'action',
        rowSelector: 'tr[data-id]',
        defaultSort: 'date',
        defaultDir: 'desc',
        colspan: 4,
    });
}
