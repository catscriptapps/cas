// /resources/js/utils/seasons/seasons-init.js

import { initSeasonsModal } from '../../modals/seasons-modal.js';
import { initDeleteSeason } from './delete-season.js';
import { initViewTeams } from './view-teams.js';
import { initViewDetails } from './view-details.js';
import { initDataTable } from '../../components/data-table.js';

/**
 * Shared bootstrap for the Schedules landing page (a Seasons list).
 */
export function initSeasonsModule() {
    initSeasonsModal();
    initDeleteSeason();
    initViewTeams();
    initViewDetails();

    initDataTable({
        tbodyId: 'seasons-tbody',
        countId: 'seasons-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/seasons`,
        resourceLabel: 'season',
        colspan: 3,
        defaultSort: 'season',
        defaultDir: 'desc',
    });
}
