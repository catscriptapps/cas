// /resources/js/utils/inspections/initials-autosave.js
//
// The inspector-initials input lives in every section's footer legend (see
// section-footer.php) -- delegated on the content container since that
// input gets redrawn on every tab switch, unlike the page-level Summary
// textarea which only needs a direct listener once.

let debounceTimer;

async function saveInitials(input) {
    const inspectionId = document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
    if (!inspectionId) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-initials`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, initials: input.value }),
        });
        const result = await response.json();

        const indicator = document.getElementById('inspection-initials-save-indicator');
        if (result.success && indicator) {
            indicator.classList.remove('hidden');
            clearTimeout(indicator._hideTimer);
            indicator._hideTimer = setTimeout(() => indicator.classList.add('hidden'), 1500);
        }
    } catch (err) {
        console.error('Failed to save inspector initials:', err);
    }
}

export function initInitialsAutosave() {
    const container = document.getElementById('inspection-section-content');
    if (!container || container._initialsAutosaveAttached) return;
    container._initialsAutosaveAttached = true;

    container.addEventListener('input', (e) => {
        if (e.target.id !== 'inspection-initials-input') return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => saveInitials(e.target), 700);
    });
}
