// /resources/js/pages/schedules-page.js

import { initSeasonsModule } from '../utils/seasons/seasons-init.js';
import { initScheduleDetail } from '../utils/schedules/schedule-detail.js';

export function init() {
    initSeasonsModule();
    initScheduleDetail();
}
