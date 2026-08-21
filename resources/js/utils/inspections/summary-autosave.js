// /resources/js/utils/inspections/summary-autosave.js

let debounceTimer;

async function saveSummary(textarea) {
    const inspectionId = document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
    if (!inspectionId) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-summary`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, inspection_summary: textarea.value }),
        });
        const result = await response.json();

        const indicator = document.getElementById('inspection-summary-save-indicator');
        if (result.success && indicator) {
            indicator.classList.remove('hidden');
            clearTimeout(indicator._hideTimer);
            indicator._hideTimer = setTimeout(() => indicator.classList.add('hidden'), 1500);
        }
    } catch (err) {
        console.error('Failed to save inspection summary:', err);
    }
}

/**
 * The summary textarea lives at page level, outside #inspection-section-content,
 * so it's never replaced by a tab switch -- a direct (non-delegated) listener
 * is fine here.
 */
export function initSummaryAutosave() {
    const textarea = document.getElementById('inspection-summary-textarea');
    if (!textarea || textarea._summaryAutosaveAttached) return;
    textarea._summaryAutosaveAttached = true;

    textarea.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => saveSummary(textarea), 700);
    });
}
