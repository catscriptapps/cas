// /resources/js/utils/seasons/view-details.js

import { loadPartial } from '../spa-router.js';

/**
 * SPA navigation from a season row's "View" trigger to its detail page --
 * /schedules/{encodedId}, /stats/{encodedId}, or /gamesheets/{encodedId}
 * depending on which list page rendered the row (see data-row.php's
 * $contextConfig).
 */
export function initViewDetails() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-schedule-trigger, .view-stats-trigger, .view-gamesheet-trigger');
        if (!trigger) return;

        e.preventDefault();

        let resource = 'schedules';
        if (trigger.classList.contains('view-stats-trigger')) resource = 'stats';
        if (trigger.classList.contains('view-gamesheet-trigger')) resource = 'gamesheets';
        const encodedId = trigger.dataset.encodedId;
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const detailUrl = `${baseUrl}${resource}/${encodedId}`;

        loadPartial(detailUrl);
    });
}
