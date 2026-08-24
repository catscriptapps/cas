// /resources/js/pages/league-management-page.js

import { initTabSwitcher } from '../ui/tab-switcher.js';
import { initDataTable } from '../components/data-table.js';
import { initSportsModal } from '../modals/sports-modal.js';
import { initLeaguesModal } from '../modals/leagues-modal.js';
import { initDivisionsModal } from '../modals/divisions-modal.js';
import { initDeleteSport } from '../utils/sports/delete-sport.js';
import { initDeleteLeague } from '../utils/leagues/delete-league.js';
import { initDeleteDivision } from '../utils/divisions/delete-division.js';

export function init() {
    initTabSwitcher('#league-mgmt-tabs');

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    initDataTable({
        tbodyId: 'sports-tbody',
        countId: 'sports-count',
        endpoint: `${baseUrl}api/sports`,
        resourceLabel: 'sport',
        colspan: 4,
        defaultSort: 'sport',
        defaultDir: 'asc',
    });

    initDataTable({
        tbodyId: 'leagues-tbody',
        countId: 'leagues-count',
        endpoint: `${baseUrl}api/leagues`,
        resourceLabel: 'league',
        colspan: 5,
        defaultSort: 'league',
        defaultDir: 'asc',
    });

    initDataTable({
        tbodyId: 'divisions-tbody',
        countId: 'divisions-count',
        endpoint: `${baseUrl}api/divisions`,
        resourceLabel: 'division',
        colspan: 6,
        defaultSort: 'division',
        defaultDir: 'asc',
    });

    initSportsModal();
    initLeaguesModal();
    initDivisionsModal();

    initDeleteSport();
    initDeleteLeague();
    initDeleteDivision();
}
