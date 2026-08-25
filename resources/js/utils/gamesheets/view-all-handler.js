// /resources/js/utils/gamesheets/view-all-handler.js

import { loadPartial } from '../spa-router.js';
import { showSpinner, hideSpinner } from '../../ui/spinner.js';
import { initGamesFilters } from '../schedules/games-filter.js';

export function handleViewAll() {
    const viewAllBtn = document.querySelector('#view-all-schedules-btn');
    const contentArea = document.querySelector('#schedule-content-area');
    const backBtn = document.querySelector('#back-to-schedules-btn');
    const pdfBtn = document.querySelector('#export-pdf-btn');

    if (!viewAllBtn || !contentArea) return;

    viewAllBtn.addEventListener('click', async () => {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';

        showSpinner();

        try {
            const response = await fetch(`${baseUrl}api/gamesheets?action=view_all`);
            const result = await response.json();

            if (!result.success) return;

            contentArea.innerHTML = result.html;

            viewAllBtn.classList.add('hidden');
            // No season context spans "all divisions", so PDF export (which
            // needs a single season_id) isn't available in this mode.
            if (pdfBtn) pdfBtn.classList.add('hidden');

            const headerTitleContainer = document.querySelector('h1.text-2xl');
            if (headerTitleContainer) {
                headerTitleContainer.innerHTML = `
                    Gamesheets for <span class="text-primary-600">All Divisions</span>
                    <span class="text-gray-400 font-light ml-1">Active</span>
                `;
            }

            if (backBtn) {
                backBtn.innerHTML = `
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Division
                `;
                backBtn.removeAttribute('data-partial');
                backBtn.onclick = (e) => {
                    e.preventDefault();
                    loadPartial(window.location.href);
                };
            }

            // The click/change delegation in accordion-handler.js is bound to
            // #schedule-content-area itself, so it survives this innerHTML
            // swap untouched -- only the filter inputs need fresh listeners.
            initGamesFilters();

            contentArea.scrollIntoView({ behavior: 'smooth' });
        } catch (err) {
            console.error('Error fetching master gamesheets:', err);
        } finally {
            hideSpinner();
        }
    });
}
