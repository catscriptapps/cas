// /resources/js/utils/inspections/section-comment-autosave.js

let debounceTimer;

async function saveComment(textarea) {
    const inspectionId = document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
    const sectionId = document.getElementById('section-content-inner')?.dataset.sectionId;
    if (!inspectionId || !sectionId) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-section-comment`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, section_id: sectionId, comments: textarea.value }),
        });
        const result = await response.json();

        const indicator = document.getElementById('section-comment-save-indicator');
        if (result.success && indicator) {
            indicator.classList.remove('hidden');
            clearTimeout(indicator._hideTimer);
            indicator._hideTimer = setTimeout(() => indicator.classList.add('hidden'), 1500);
        }
    } catch (err) {
        console.error('Failed to save section comment:', err);
    }
}

export function initSectionCommentAutosave() {
    const container = document.getElementById('inspection-section-content');
    if (!container || container._commentAutosaveAttached) return;
    container._commentAutosaveAttached = true;

    container.addEventListener('input', (e) => {
        if (!e.target.matches('#section-comment-textarea')) return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => saveComment(e.target), 600);
    });
}
