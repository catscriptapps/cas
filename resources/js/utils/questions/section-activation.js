// /resources/js/utils/questions/section-activation.js

import { loadQuestionsForSection } from './load-questions.js';
import { loadDiagramsForSection } from './diagrams.js';

// Split out from section-tabs.js so both it and section-form-submit.js can
// import activateSectionTab() without creating an import cycle through
// sections-modal.js (which section-tabs.js also depends on).

/**
 * Marks a tab as the active section: restyles it, updates the toolbar
 * heading, and loads that section's questions.
 *
 * Each tab carries its own active/inactive class strings as data attributes
 * (see SectionsController::renderTab()) rather than a single shared pair
 * here, since every section's resting ("inactive") color is now its own
 * icon-tinted personality instead of one uniform gray -- PHP is the single
 * source of truth for that color map, this just toggles between whatever
 * that one button says its two states are.
 */
export function activateSectionTab(encodedId) {
    const tabsWrapper = document.getElementById('section-tabs-wrapper');
    if (!tabsWrapper) return;

    tabsWrapper.dataset.activeSectionId = encodedId;

    tabsWrapper.querySelectorAll('.section-tab-btn').forEach((btn) => {
        const isActive = btn.dataset.id === encodedId;
        const activeClasses = (btn.dataset.activeClasses || '').split(' ').filter(Boolean);
        const inactiveClasses = (btn.dataset.inactiveClasses || '').split(' ').filter(Boolean);
        btn.classList.remove(...activeClasses, ...inactiveClasses);
        btn.classList.add(...(isActive ? activeClasses : inactiveClasses));
    });

    const activeBtn = tabsWrapper.querySelector(`.section-tab-btn[data-id="${encodedId}"]`);
    const nameEl = document.getElementById('active-section-name');
    if (nameEl) nameEl.textContent = activeBtn?.dataset.name || '';

    // Covers the "just added the first section" case too, not just normal
    // tab switching -- harmless no-op when these are already visible.
    document.getElementById('sections-empty-state')?.classList.add('hidden');
    document.getElementById('section-content-header')?.classList.remove('hidden');
    document.getElementById('section-content')?.classList.remove('hidden');

    loadQuestionsForSection(encodedId);
    loadDiagramsForSection(encodedId);
}
