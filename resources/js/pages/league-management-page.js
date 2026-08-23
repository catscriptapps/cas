// /resources/js/pages/league-management-page.js

import { initTabSwitcher } from '../ui/tab-switcher.js';
import { enableTableSearch } from '../components/table-search.js';
import { initSportsModal } from '../modals/sports-modal.js';
import { initLeaguesModal } from '../modals/leagues-modal.js';
import { initDivisionsModal } from '../modals/divisions-modal.js';
import { initDeleteSport } from '../utils/sports/delete-sport.js';
import { initDeleteLeague } from '../utils/leagues/delete-league.js';
import { initDeleteDivision } from '../utils/divisions/delete-division.js';

export function init() {
    initTabSwitcher('#league-mgmt-tabs');

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    enableTableSearch({
        searchInputId: 'sports-search',
        tbodyId: 'sports-tbody',
        countId: 'sports-count',
        endpoint: `${baseUrl}api/sports`,
        resourceLabel: 'sport',
        addButtonId: 'add-sport-btn',
    });

    enableTableSearch({
        searchInputId: 'leagues-search',
        tbodyId: 'leagues-tbody',
        countId: 'leagues-count',
        endpoint: `${baseUrl}api/leagues`,
        resourceLabel: 'league',
        addButtonId: 'add-league-btn',
    });

    enableTableSearch({
        searchInputId: 'divisions-search',
        tbodyId: 'divisions-tbody',
        countId: 'divisions-count',
        endpoint: `${baseUrl}api/divisions`,
        resourceLabel: 'division',
        addButtonId: 'add-division-btn',
    });

    initSportsModal();
    initLeaguesModal();
    initDivisionsModal();

    initDeleteSport();
    initDeleteLeague();
    initDeleteDivision();
}
