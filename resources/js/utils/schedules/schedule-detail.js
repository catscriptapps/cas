// /resources/js/utils/schedules/schedule-detail.js

import { initSchedulesModal } from '../../modals/schedules-modal.js';
import { initDeleteSchedule } from './delete-schedule.js';
import { handleViewAll } from './view-all-handler.js';
import { initSchedulesExport } from './export-pdf.js';
import { initGamesFilters } from './games-filter.js';

export function initScheduleDetail() {
    initSchedulesModal();
    initDeleteSchedule('.space-y-6');
    handleViewAll();
    initSchedulesExport();
    initGamesFilters();
}
