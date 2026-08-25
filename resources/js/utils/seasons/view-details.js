// /resources/js/utils/seasons/view-details.js

import { loadPartial } from '../spa-router.js';

/**
 * SPA navigation from a season row's "View" trigger to its schedule detail
 * page (/schedules/{encodedId}).
 */
export function initViewDetails() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-schedule-trigger');
        if (!trigger) return;

        e.preventDefault();

        const encodedId = trigger.dataset.encodedId;
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const detailUrl = `${baseUrl}schedules/${encodedId}`;

        loadPartial(detailUrl);
    });
}
