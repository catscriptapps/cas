// /resources/js/pages/inspections-page.js
//
// Single entry point for BOTH the Inspections list page (/inspections) and
// the Inspection detail/fill-in page (/inspections/{encodedId}). The SPA
// router (app.js's initPageScript()) matches a page script by walking URL
// segments right-to-left looking for a "{segment}-page" manifest entry --
// for a detail route the id segment never matches anything, so it falls
// through to "inspections-page" regardless. Rather than fight that, this
// file detects which markup is actually on screen and wires up the right
// behavior set.

import { AnimationEngine } from '../utils/animations';
import { initInspectionsModal } from '../modals/inspections-modal.js';
import { initDeleteInspection } from '../utils/inspections/delete-inspection.js';
import { initInspectionsListTable } from '../utils/inspections/list-table.js';
import { initFinalizeInspection } from '../utils/inspections/finalize-inspection.js';
import { initInspectionSectionTabs, initLibraryShortcuts } from '../utils/inspections/section-tabs.js';
import { initAnswerAutosave } from '../utils/inspections/answer-autosave.js';
import { initSectionCommentAutosave } from '../utils/inspections/section-comment-autosave.js';
import { initSummaryAutosave } from '../utils/inspections/summary-autosave.js';
import { initInitialsAutosave } from '../utils/inspections/initials-autosave.js';
import { initInspectionPhotos } from '../utils/inspections/photos.js';
import { initInspectionVideos } from '../utils/inspections/videos.js';
import { initPhotoLibrary } from '../utils/inspections/photo-library.js';
import { initVideoLibrary } from '../utils/inspections/video-library.js';
import { initContracts } from '../utils/inspections/contracts.js';
import { initCopyAccessCode } from '../utils/inspections/copy-access-code.js';
import { initNoteHintsTriggers } from '../utils/inspections/note-hints-trigger.js';
import { registerImagePreview } from '../utils/globals/preview.js';

export function init() {
    AnimationEngine.refresh();

    // Shared by both pages.
    initInspectionsModal();
    initDeleteInspection();
    initCopyAccessCode();

    const isDetailPage = !!document.querySelector('[data-inspection-id]');

    if (isDetailPage) {
        initFinalizeInspection();
        initInspectionSectionTabs();
        initAnswerAutosave();
        initSectionCommentAutosave();
        initSummaryAutosave();
        initInitialsAutosave();
        initInspectionPhotos();
        initInspectionVideos();
        initPhotoLibrary();
        initVideoLibrary();
        initContracts();
        initLibraryShortcuts();
        initNoteHintsTriggers();
        // section-tabs.js/section-refresh.js re-register this after every AJAX
        // swap, but the very first section is rendered straight into the page
        // on load (see InspectionDetailController::index()), so without this
        // call its photos have no click-to-preview until the user switches
        // tabs at least once -- happens whether the report is locked or not.
        registerImagePreview();
    } else {
        initInspectionsListTable();
    }
}
