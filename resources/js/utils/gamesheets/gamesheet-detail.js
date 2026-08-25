// /resources/js/utils/gamesheets/gamesheet-detail.js

import { handleViewAll } from './view-all-handler.js';
import { initGamesheetsExport } from './export-pdf.js';
import { initGamesheetAccordion } from './accordion-handler.js';
import { initGamesFilters } from '../schedules/games-filter.js';

export function initGamesheetDetail() {
    handleViewAll();
    initGamesheetsExport();
    initGamesheetAccordion();
    initGamesFilters();
}
