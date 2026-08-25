// /resources/js/pages/stats-page.js

import { initSeasonsModule } from '../utils/seasons/seasons-init.js';
import { initStatsDetail } from '../utils/stats/stats-detail.js';

export function init() {
    initSeasonsModule('stats');
    initStatsDetail();
}
