// /resources/js/utils/inspections/photos.js
//
// A section's own photo grid: assignment happens in the Photo Library (see
// photo-library.js) -- this file only handles what's scoped to one section
// once a photo's already there: reorder-within-section, per-section
// caption, and "remove from this section" (unassign, not a real delete).

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';

let activeReorderId = null;
const descriptionTimers = new Map();
const selectedPictureIds = new Set();

function getInspectionId() {
    return document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
}

function getActiveSectionId() {
    return document.getElementById('section-content-inner')?.dataset.sectionId;
}

async function unassignFromSection(card) {
    const confirmed = await confirmDialog(
        'Remove this photo from the section? It stays in the Photo Library.',
        'Remove',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-picture-toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, target: 'section', picture_id: card.dataset.pictureId, section_id: sectionId, value: 0 }),
        });
        const result = await response.json();

        if (result.success) {
            selectedPictureIds.delete(card.dataset.pictureId);
            card.remove();
            const grid = document.getElementById('inspection-photos-grid');
            const empty = document.getElementById('inspection-photos-empty');
            if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
            updateBulkDeleteButton();
            showToast('Removed from section.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not remove photo.', 'error');
        }
    } catch (err) {
        console.error('Photo unassign failed:', err);
    }
}

function updateBulkDeleteButton() {
    const btn = document.getElementById('delete-selected-photos-btn');
    const label = document.getElementById('delete-selected-photos-label');
    const selectAll = document.getElementById('inspection-photos-select-all');
    const grid = document.getElementById('inspection-photos-grid');

    if (btn) {
        if (selectedPictureIds.size > 0) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            if (label) label.textContent = `Remove Selected (${selectedPictureIds.size})`;
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
        }
    }

    if (selectAll && grid) {
        const total = grid.querySelectorAll('.inspection-photo-card').length;
        selectAll.checked = total > 0 && selectedPictureIds.size === total;
        selectAll.indeterminate = selectedPictureIds.size > 0 && selectedPictureIds.size < total;
    }
}

async function unassignSelected() {
    if (selectedPictureIds.size === 0) return;
    const ids = Array.from(selectedPictureIds);

    const confirmed = await confirmDialog(
        ids.length > 1 ? `${ids.length} photos will be removed from this section (they stay in the library). Continue?` : 'This photo will be removed from this section (it stays in the library). Continue?',
        'Remove',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        await Promise.all(ids.map((id) => fetch(`${baseUrl}api/inspection-library-picture-toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, target: 'section', picture_id: id, section_id: sectionId, value: 0 }),
        })));

        ids.forEach((id) => {
            document.querySelector(`.inspection-photo-card[data-picture-id="${id}"]`)?.remove();
            selectedPictureIds.delete(id);
        });
        updateBulkDeleteButton();
        const grid = document.getElementById('inspection-photos-grid');
        const empty = document.getElementById('inspection-photos-empty');
        if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
        showToast('Removed from section.', 'success');
    } catch (err) {
        console.error('Bulk photo unassign failed:', err);
    }
}

function clearReorderHighlight() {
    if (!activeReorderId) return;
    document.querySelector(`.inspection-photo-card[data-picture-id="${activeReorderId}"]`)?.classList.remove('border-secondary-500');
    document.querySelector(`.inspection-photo-card[data-picture-id="${activeReorderId}"]`)?.classList.add('border-transparent');
}

function handleReorderClick(card) {
    const grid = document.getElementById('inspection-photos-grid');
    if (!grid) return;

    const clickedId = card.dataset.pictureId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        card.classList.remove('border-transparent');
        card.classList.add('border-secondary-500');
        return;
    }

    const sourceCard = grid.querySelector(`.inspection-photo-card[data-picture-id="${activeReorderId}"]`);
    clearReorderHighlight();
    activeReorderId = null;

    if (!sourceCard || sourceCard === card) return;

    const allCards = Array.from(grid.querySelectorAll('.inspection-photo-card'));
    const sourceIndex = allCards.indexOf(sourceCard);
    const targetIndex = allCards.indexOf(card);
    if (sourceIndex === targetIndex) return;

    sourceCard.remove();
    if (sourceIndex > targetIndex) {
        card.before(sourceCard);
    } else {
        card.after(sourceCard);
    }

    persistOrder();
}

async function persistOrder() {
    const grid = document.getElementById('inspection-photos-grid');
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('.inspection-photo-card')).map((c) => c.dataset.pictureId);
    if (!ids.length) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-picture-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, target: 'section', section_id: sectionId, ids }),
        });
        const result = await response.json();
        if (result.success) showToast('Photo order updated.', 'success');
    } catch (err) {
        console.error('Failed to reorder photos:', err);
    }
}

async function saveDescription(input) {
    const card = input.closest('.inspection-photo-card');
    if (!card) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        await fetch(`${baseUrl}api/inspection-library-picture-caption`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, target: 'section', picture_id: card.dataset.pictureId, section_id: sectionId, description: input.value }),
        });
    } catch (err) {
        console.error('Failed to save photo caption:', err);
    }
}

export function initInspectionPhotos() {
    const container = document.getElementById('inspection-section-content');
    if (!container || container._photosAttached) return;
    container._photosAttached = true;

    container.addEventListener('click', (e) => {
        const deleteSelectedBtn = e.target.closest('#delete-selected-photos-btn');
        if (deleteSelectedBtn) {
            unassignSelected();
            return;
        }

        const removeBtn = e.target.closest('[data-action="unassign-picture-section"]');
        if (removeBtn) {
            const card = removeBtn.closest('.inspection-photo-card');
            if (card) unassignFromSection(card);
            return;
        }

        const reorderBtn = e.target.closest('[data-action="reorder-picture"]');
        if (reorderBtn) {
            const card = reorderBtn.closest('.inspection-photo-card');
            if (card && card.dataset.mode === 'section') handleReorderClick(card);
        }
    });

    container.addEventListener('change', (e) => {
        if (e.target.id === 'inspection-photos-select-all') {
            const grid = document.getElementById('inspection-photos-grid');
            const checkboxes = grid ? grid.querySelectorAll('[data-action="select-picture"]') : [];
            checkboxes.forEach((cb) => {
                cb.checked = e.target.checked;
                if (e.target.checked) selectedPictureIds.add(cb.dataset.id);
                else selectedPictureIds.delete(cb.dataset.id);
            });
            updateBulkDeleteButton();
            return;
        }

        const checkbox = e.target.closest('[data-action="select-picture"]');
        if (checkbox && checkbox.closest('.inspection-photo-card')?.dataset.mode === 'section') {
            if (checkbox.checked) selectedPictureIds.add(checkbox.dataset.id);
            else selectedPictureIds.delete(checkbox.dataset.id);
            updateBulkDeleteButton();
        }
    });

    container.addEventListener('input', (e) => {
        if (!e.target.matches('[data-role="picture-description"]')) return;
        const card = e.target.closest('.inspection-photo-card');
        if (card?.dataset.mode !== 'section') return;
        const key = card.dataset.pictureId;
        clearTimeout(descriptionTimers.get(key));
        descriptionTimers.set(key, setTimeout(() => saveDescription(e.target), 600));
    });

    // The grid gets fully redrawn on tab switch -- forget any stale
    // selection rather than let it silently reference removed elements.
    container.addEventListener('section-content-updated', () => {
        selectedPictureIds.clear();
        updateBulkDeleteButton();
    });
}
