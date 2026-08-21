// /resources/js/pages/history-page.js

import { initHistoryListTable } from '../utils/history/list-table.js';
import { initArchiveActivity } from '../utils/history/archive-activity.js';
import { initDeleteHistory } from '../utils/history/delete-history.js';

/**
 * Initialize the History page JS.
 */
export function init() {
    initHistoryListTable();
    initArchiveActivity();
    initDeleteHistory();
}