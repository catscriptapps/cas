// /resources/js/utils/schedules/view-all-handler.js

import { loadPartial } from '../spa-router.js';
import { initDeleteSchedule } from './delete-schedule.js';
import { initSchedulesModal } from '../../modals/schedules-modal.js';
import { showSpinner, hideSpinner } from '../../ui/spinner.js';
import { initGamesFilters } from './games-filter.js';

/**
 * Fetches and swaps in the master (all-divisions) schedule. Exported (not
 * just wired to the button's own click listener) so schedules/form-submit.js
 * can await this same refresh after a save while already in View All mode,
 * instead of faking a `.click()` on the button with no way to know when the
 * resulting fetch has actually finished -- it needs that to scroll to the
 * saved game afterward.
 */
export async function refreshMasterSchedule() {
    const viewAllBtn = document.querySelector('#view-all-schedules-btn');
    const contentArea = document.querySelector('#schedule-content-area');
    const addBtn = document.querySelector('#add-schedule-btn');
    const backBtn = document.querySelector('#back-to-schedules-btn');

    if (!contentArea) return false;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    showSpinner();

    try {
        const response = await fetch(`${baseUrl}api/schedules?action=view_all`);
        const result = await response.json();

        if (!result.success) return false;

        contentArea.innerHTML = result.html;

        if (addBtn) addBtn.classList.add('hidden');
        if (viewAllBtn) viewAllBtn.classList.add('hidden');

        const headerTitleContainer = document.querySelector('h1.text-2xl');
        if (headerTitleContainer) {
            headerTitleContainer.innerHTML = `
                Schedule for <span class="text-primary-600">All Divisions</span>
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

        initDeleteSchedule('#schedule-content-area');
        initSchedulesModal();
        initGamesFilters();

        contentArea.scrollIntoView({ behavior: 'smooth' });
        return true;
    } catch (err) {
        console.error('Error fetching master schedule:', err);
        return false;
    } finally {
        hideSpinner();
    }
}

export function handleViewAll() {
    const viewAllBtn = document.querySelector('#view-all-schedules-btn');
    if (!viewAllBtn || !document.querySelector('#schedule-content-area')) return;

    viewAllBtn.addEventListener('click', () => refreshMasterSchedule());
}
