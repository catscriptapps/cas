// /resources/js/utils/stats/stats-detail.js

import { initTabSwitcher } from './tab-switcher.js';
import { initStatsAutoSave } from './auto-save-handler.js';
import { initStatsExport } from './export-pdf.js';

export function initStatsDetail() {
    initTabSwitcher();
    initStatsAutoSave();
    initStatsExport();
}
