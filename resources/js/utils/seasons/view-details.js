// /resources/js/utils/seasons/view-details.js

import { loadPartial } from '../spa-router.js';

/**
 * SPA navigation from a season row's "View" trigger to its detail page --
 * either /schedules/{encodedId} or /stats/{encodedId}, depending on which
 * list page rendered the row (see data-row.php's $contextConfig).
 */
export function initViewDetails() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-schedule-trigger, .view-stats-trigger');
        if (!trigger) return;

        e.preventDefault();

        const resource = trigger.classList.contains('view-stats-trigger') ? 'stats' : 'schedules';
        const encodedId = trigger.dataset.encodedId;
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const detailUrl = `${baseUrl}${resource}/${encodedId}`;

        loadPartial(detailUrl);
    });
}
