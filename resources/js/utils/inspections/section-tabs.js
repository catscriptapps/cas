// /resources/js/utils/inspections/section-tabs.js

import { registerImagePreview } from '../../utils/globals/preview.js';

// The three non-section tabs (see InspectionDetailController::VIRTUAL_TABS)
// use plain literal ids instead of an IdEncoder-encoded section id -- hit a
// different endpoint (no section_id to resolve) than a real section tab.
const VIRTUAL_TABS = new Set(['library-photos', 'library-videos', 'contracts']);

// Each tab carries its own active/inactive class strings as data attributes
// (see InspectionDetailController::renderTab()) since a section's resting
// color is now its own icon tint instead of one uniform gray -- PHP is the
// single source of truth for that color map.
function setTabActive(btn, isActive) {
    const activeClasses = (btn.dataset.activeClasses || '').split(' ').filter(Boolean);
    const inactiveClasses = (btn.dataset.inactiveClasses || '').split(' ').filter(Boolean);
    btn.classList.remove(...activeClasses, ...inactiveClasses);
    btn.classList.add(...(isActive ? activeClasses : inactiveClasses));
}

/**
 * Syncs the Photo/Video Library tabs' live count badges (data-count-key,
 * see InspectionDetailController::renderVirtualTab()) from a
 * library-content/inspection-detail response's `counts` field -- called
 * after every tab load/refresh so the badge never drifts from what's
 * actually in the library.
 */
export function updateTabCounts(counts) {
    if (!counts) return;
    Object.entries(counts).forEach(([key, count]) => {
        const badge = document.querySelector(`[data-role="tab-count"][data-count-key="${key}"]`);
        if (badge) badge.textContent = String(count);
    });
}

/**
 * Switches the active tab and loads its content -- shared by the tab bar's
 * own click handler and the "Add from Photo/Video Library" shortcut links
 * embedded in a section's/Contracts' content (see initLibraryShortcuts()).
 */
export async function switchToTab(tabId) {
    const wrapper = document.getElementById('inspection-section-tabs-wrapper');
    const contentContainer = document.getElementById('inspection-section-content');
    if (!wrapper || !contentContainer) return;
    if (tabId === wrapper.dataset.activeSectionId) return;

    const btn = wrapper.querySelector(`[data-action="select-inspection-section"][data-id="${tabId}"]`);
    if (!btn) return;

    const inspectionId = document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    wrapper.querySelectorAll('.inspection-section-tab-btn').forEach((b) => setTabActive(b, false));
    setTabActive(btn, true);
    wrapper.dataset.activeSectionId = tabId;

    contentContainer.innerHTML = '<div class="py-16 text-center text-gray-400 text-sm font-bold">Loading&hellip;</div>';

    try {
        const url = VIRTUAL_TABS.has(tabId)
            ? `${baseUrl}api/inspection-library-content?inspection_id=${encodeURIComponent(inspectionId)}&tab=${encodeURIComponent(tabId)}`
            : `${baseUrl}api/inspection-detail?inspection_id=${encodeURIComponent(inspectionId)}&section_id=${encodeURIComponent(tabId)}`;
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const result = await response.json();

        if (result.success) {
            contentContainer.innerHTML = result.html;
            registerImagePreview();
            updateTabCounts(result.counts);
            contentContainer.dispatchEvent(new CustomEvent('section-content-updated'));
        } else {
            contentContainer.innerHTML = `<div class="py-16 text-center text-red-500 text-sm font-bold">${result.messages?.[0] || 'Failed to load section.'}</div>`;
        }
    } catch (err) {
        console.error('Failed to load section:', err);
        contentContainer.innerHTML = '<div class="py-16 text-center text-red-500 text-sm font-bold">Failed to load section.</div>';
    }
}

/**
 * Switching sections is a plain tab-click (no reorder -- sections themselves
 * are the company's shared template, not something to rearrange from here).
 */
export function initInspectionSectionTabs() {
    const wrapper = document.getElementById('inspection-section-tabs-wrapper');
    if (!wrapper) return;

    wrapper.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="select-inspection-section"]');
        if (!btn) return;
        switchToTab(btn.dataset.id);
    });
}

/**
 * "Add from Photo/Video Library" links embedded in a section's photo/video
 * grid, and in the Contracts tab, jump straight to the matching library tab
 * -- delegated on the whole page since these links live inside the content
 * area that gets fully redrawn on every tab switch.
 */
export function initLibraryShortcuts() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="switch-to-library"]');
        if (!btn) return;
        switchToTab(btn.dataset.tab);
    });
}
