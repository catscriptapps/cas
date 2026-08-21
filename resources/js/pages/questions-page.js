// /resources/js/pages/questions-page.js

import { AnimationEngine } from '../utils/animations';
import { initSectionsModal } from '../modals/sections-modal.js';
import { initSectionTabs } from '../utils/questions/section-tabs.js';
import { initQuestionsModal } from '../modals/questions-modal.js';
import { initDeleteQuestion } from '../utils/questions/delete-question.js';
import { initReorderQuestions } from '../utils/questions/reorder-questions.js';
import { initSectionDiagrams, loadDiagramsForSection } from '../utils/questions/diagrams.js';

/**
 * The page already server-renders the active section's tab (styled active)
 * and its questions on first load -- this just syncs the toolbar heading to
 * match, without triggering a redundant AJAX re-fetch of what's already on
 * screen.
 */
function syncActiveSectionHeading() {
    const tabsWrapper = document.getElementById('section-tabs-wrapper');
    const activeId = tabsWrapper?.dataset.activeSectionId;
    if (!activeId) return;

    const activeBtn = tabsWrapper.querySelector(`.section-tab-btn[data-id="${activeId}"]`);
    const nameEl = document.getElementById('active-section-name');
    if (nameEl) nameEl.textContent = activeBtn?.dataset.name || '';

    const countLabel = document.getElementById('questions-count');
    if (countLabel) countLabel.textContent = String(document.querySelectorAll('#questions-list [data-question-id]').length);
}

/**
 * Initialize the Questions page JS.
 */
export function init() {
    AnimationEngine.refresh();

    initSectionsModal();
    initSectionTabs();
    initQuestionsModal();
    initDeleteQuestion();
    initReorderQuestions();
    initSectionDiagrams();

    syncActiveSectionHeading();

    // Diagrams aren't server-rendered into the initial page load like
    // questions are (see questions.php) -- fetch the active section's
    // diagrams once here; every subsequent section switch handles this
    // itself via activateSectionTab() -> loadDiagramsForSection().
    const activeSectionId = document.getElementById('section-tabs-wrapper')?.dataset.activeSectionId;
    if (activeSectionId) loadDiagramsForSection(activeSectionId);
}
