// /resources/js/utils/inspections/videos.js
//
// A section's own video grid -- same shape as photos.js. Assignment happens
// in the Video Library (see video-library.js); this file only handles
// reorder-within-section, per-section caption, and "remove from this
// section" (unassign, not a real delete).

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';

let activeReorderId = null;
const descriptionTimers = new Map();
const selectedVideoIds = new Set();

function getInspectionId() {
    return document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
}

function getActiveSectionId() {
    return document.getElementById('section-content-inner')?.dataset.sectionId;
}

async function unassignFromSection(card) {
    const confirmed = await confirmDialog(
        'Remove this video from the section? It stays in the Video Library.',
        'Remove',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-video-toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, video_id: card.dataset.videoId, section_id: sectionId, value: 0 }),
        });
        const result = await response.json();

        if (result.success) {
            selectedVideoIds.delete(card.dataset.videoId);
            card.remove();
            const grid = document.getElementById('inspection-videos-grid');
            const empty = document.getElementById('inspection-videos-empty');
            if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
            updateBulkDeleteButton();
            showToast('Removed from section.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not remove video.', 'error');
        }
    } catch (err) {
        console.error('Video unassign failed:', err);
    }
}

function updateBulkDeleteButton() {
    const btn = document.getElementById('delete-selected-videos-btn');
    const label = document.getElementById('delete-selected-videos-label');
    const selectAll = document.getElementById('inspection-videos-select-all');
    const grid = document.getElementById('inspection-videos-grid');

    if (btn) {
        if (selectedVideoIds.size > 0) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            if (label) label.textContent = `Remove Selected (${selectedVideoIds.size})`;
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
        }
    }

    if (selectAll && grid) {
        const total = grid.querySelectorAll('.inspection-video-card').length;
        selectAll.checked = total > 0 && selectedVideoIds.size === total;
        selectAll.indeterminate = selectedVideoIds.size > 0 && selectedVideoIds.size < total;
    }
}

async function unassignSelected() {
    if (selectedVideoIds.size === 0) return;
    const ids = Array.from(selectedVideoIds);

    const confirmed = await confirmDialog(
        ids.length > 1 ? `${ids.length} videos will be removed from this section (they stay in the library). Continue?` : 'This video will be removed from this section (it stays in the library). Continue?',
        'Remove',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        await Promise.all(ids.map((id) => fetch(`${baseUrl}api/inspection-library-video-toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, video_id: id, section_id: sectionId, value: 0 }),
        })));

        ids.forEach((id) => {
            document.querySelector(`.inspection-video-card[data-video-id="${id}"]`)?.remove();
            selectedVideoIds.delete(id);
        });
        updateBulkDeleteButton();
        const grid = document.getElementById('inspection-videos-grid');
        const empty = document.getElementById('inspection-videos-empty');
        if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
        showToast('Removed from section.', 'success');
    } catch (err) {
        console.error('Bulk video unassign failed:', err);
    }
}

function clearReorderHighlight() {
    if (!activeReorderId) return;
    document.querySelector(`.inspection-video-card[data-video-id="${activeReorderId}"]`)?.classList.remove('border-secondary-500');
    document.querySelector(`.inspection-video-card[data-video-id="${activeReorderId}"]`)?.classList.add('border-transparent');
}

function handleReorderClick(card) {
    const grid = document.getElementById('inspection-videos-grid');
    if (!grid) return;

    const clickedId = card.dataset.videoId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        card.classList.remove('border-transparent');
        card.classList.add('border-secondary-500');
        return;
    }

    const sourceCard = grid.querySelector(`.inspection-video-card[data-video-id="${activeReorderId}"]`);
    clearReorderHighlight();
    activeReorderId = null;

    if (!sourceCard || sourceCard === card) return;

    const allCards = Array.from(grid.querySelectorAll('.inspection-video-card'));
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
    const grid = document.getElementById('inspection-videos-grid');
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('.inspection-video-card')).map((c) => c.dataset.videoId);
    if (!ids.length) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-video-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, section_id: sectionId, ids }),
        });
        const result = await response.json();
        if (result.success) showToast('Video order updated.', 'success');
    } catch (err) {
        console.error('Failed to reorder videos:', err);
    }
}

async function saveDescription(input) {
    const card = input.closest('.inspection-video-card');
    if (!card) return;

    const inspectionId = getInspectionId();
    const sectionId = getActiveSectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        await fetch(`${baseUrl}api/inspection-library-video-caption`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, video_id: card.dataset.videoId, section_id: sectionId, description: input.value }),
        });
    } catch (err) {
        console.error('Failed to save video caption:', err);
    }
}

export function initInspectionVideos() {
    const container = document.getElementById('inspection-section-content');
    if (!container || container._videosAttached) return;
    container._videosAttached = true;

    container.addEventListener('click', (e) => {
        const deleteSelectedBtn = e.target.closest('#delete-selected-videos-btn');
        if (deleteSelectedBtn) {
            unassignSelected();
            return;
        }

        const removeBtn = e.target.closest('[data-action="unassign-video-section"]');
        if (removeBtn) {
            const card = removeBtn.closest('.inspection-video-card');
            if (card) unassignFromSection(card);
            return;
        }

        const reorderBtn = e.target.closest('[data-action="reorder-video"]');
        if (reorderBtn) {
            const card = reorderBtn.closest('.inspection-video-card');
            if (card && card.dataset.mode === 'section') handleReorderClick(card);
        }
    });

    container.addEventListener('change', (e) => {
        if (e.target.id === 'inspection-videos-select-all') {
            const grid = document.getElementById('inspection-videos-grid');
            const checkboxes = grid ? grid.querySelectorAll('[data-action="select-video"]') : [];
            checkboxes.forEach((cb) => {
                cb.checked = e.target.checked;
                if (e.target.checked) selectedVideoIds.add(cb.dataset.id);
                else selectedVideoIds.delete(cb.dataset.id);
            });
            updateBulkDeleteButton();
            return;
        }

        const checkbox = e.target.closest('[data-action="select-video"]');
        if (checkbox && checkbox.closest('.inspection-video-card')?.dataset.mode === 'section') {
            if (checkbox.checked) selectedVideoIds.add(checkbox.dataset.id);
            else selectedVideoIds.delete(checkbox.dataset.id);
            updateBulkDeleteButton();
        }
    });

    container.addEventListener('input', (e) => {
        if (!e.target.matches('[data-role="video-description"]')) return;
        const card = e.target.closest('.inspection-video-card');
        if (card?.dataset.mode !== 'section') return;
        const key = card.dataset.videoId;
        clearTimeout(descriptionTimers.get(key));
        descriptionTimers.set(key, setTimeout(() => saveDescription(e.target), 600));
    });

    container.addEventListener('section-content-updated', () => {
        selectedVideoIds.clear();
        updateBulkDeleteButton();
    });
}
