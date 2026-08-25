// /resources/js/utils/seasons/seasons-init.js

import { initSeasonsModal } from '../../modals/seasons-modal.js';
import { initDeleteSeason } from './delete-season.js';
import { initViewTeams } from './view-teams.js';
import { initViewDetails } from './view-details.js';
import { initDataTable } from '../../components/data-table.js';

/**
 * Shared bootstrap for the Seasons list, reused by both the Schedules and
 * Stats+Standings landing pages -- `pageContext` is threaded through to the
 * data-table fetch so filtering/sorting/pagination re-renders rows with the
 * right "View" trigger (see SeasonsController::index()'s $_GET['context']).
 */
export function initSeasonsModule(pageContext = 'schedules') {
    initSeasonsModal();
    initDeleteSeason();
    initViewTeams();
    initViewDetails();

    initDataTable({
        tbodyId: 'seasons-tbody',
        countId: 'seasons-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/seasons`,
        resourceLabel: 'season',
        colspan: 5,
        defaultSort: 'year',
        defaultDir: 'desc',
        extraParams: { context: pageContext },
    });
}
