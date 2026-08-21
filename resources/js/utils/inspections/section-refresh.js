// /resources/js/utils/inspections/section-refresh.js

import { registerImagePreview } from '../../utils/globals/preview.js';
import { updateTabCounts } from './section-tabs.js';

const VIRTUAL_TABS = new Set(['library-photos', 'library-videos', 'contracts']);

/**
 * Re-fetches the currently active tab's content (same endpoints tab
 * switching uses -- real section or one of the three virtual tabs) and
 * redraws #inspection-section-content in place -- used after a library
 * upload completes so the grid reflects the authoritative server-rendered
 * state without a page reload.
 */
export async function refreshActiveSectionContent() {
    const wrapper = document.getElementById('inspection-section-tabs-wrapper');
    const contentContainer = document.getElementById('inspection-section-content');
    const inspectionId = document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
    const activeId = wrapper?.dataset.activeSectionId;
    if (!contentContainer || !inspectionId || !activeId) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const url = VIRTUAL_TABS.has(activeId)
            ? `${baseUrl}api/inspection-library-content?inspection_id=${encodeURIComponent(inspectionId)}&tab=${encodeURIComponent(activeId)}`
            : `${baseUrl}api/inspection-detail?inspection_id=${encodeURIComponent(inspectionId)}&section_id=${encodeURIComponent(activeId)}`;
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const result = await response.json();

        if (result.success) {
            contentContainer.innerHTML = result.html;
            registerImagePreview();
            updateTabCounts(result.counts);
            contentContainer.dispatchEvent(new CustomEvent('section-content-updated'));
        }
    } catch (err) {
        console.error('Failed to refresh section content:', err);
    }
}
