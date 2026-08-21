// /resources/js/pages/report-page.js
//
// Entry point for the public /report/{code} guest report page. Mirrors the
// authenticated inspection detail page's section-tab switching (see
// utils/inspections/section-tabs.js), but reads the access code straight
// out of the DOM (no session) and fetches from the guest-safe
// /api/report-detail endpoint instead of the authenticated /api/inspection-detail.

import { registerImagePreview } from '../utils/globals/preview.js';

// Each tab carries its own active/inactive class strings as data attributes
// (see InspectionDetailController::renderTab(), reused verbatim here) since
// a section's resting color is its own icon tint, not one uniform gray.
function setTabActive(btn, isActive) {
    const activeClasses = (btn.dataset.activeClasses || '').split(' ').filter(Boolean);
    const inactiveClasses = (btn.dataset.inactiveClasses || '').split(' ').filter(Boolean);
    btn.classList.remove(...activeClasses, ...inactiveClasses);
    btn.classList.add(...(isActive ? activeClasses : inactiveClasses));
}

function initReportSectionTabs() {
    const wrapper = document.getElementById('report-section-tabs-wrapper');
    const contentContainer = document.getElementById('report-section-content');
    if (!wrapper || !contentContainer) return;

    const accessCode = wrapper.dataset.accessCode || '';

    wrapper.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-action="select-inspection-section"]');
        if (!btn) return;

        const encodedId = btn.dataset.id;
        if (encodedId === wrapper.dataset.activeSectionId) return;

        const baseUrl = window.APP_CONFIG?.baseUrl || '/';

        wrapper.querySelectorAll('.inspection-section-tab-btn').forEach((b) => setTabActive(b, false));
        setTabActive(btn, true);
        wrapper.dataset.activeSectionId = encodedId;

        contentContainer.innerHTML = '<div class="py-16 text-center text-gray-400 text-sm font-bold">Loading&hellip;</div>';

        try {
            const url = `${baseUrl}api/report-detail?code=${encodeURIComponent(accessCode)}&section_id=${encodeURIComponent(encodedId)}`;
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const result = await response.json();

            if (result.success) {
                contentContainer.innerHTML = result.html;
                registerImagePreview();
            } else {
                contentContainer.innerHTML = `<div class="py-16 text-center text-red-500 text-sm font-bold">${result.messages?.[0] || 'Failed to load section.'}</div>`;
            }
        } catch (err) {
            console.error('Failed to load report section:', err);
            contentContainer.innerHTML = '<div class="py-16 text-center text-red-500 text-sm font-bold">Failed to load section.</div>';
        }
    });
}

export function init() {
    initReportSectionTabs();
    registerImagePreview();
}
