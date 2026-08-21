// /resources/js/utils/inspections/video-library.js
//
// The Video Library tab -- same idea as photo-library.js, for videos (no
// Contracts checkbox; that slot is photos-only).

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';
import { videoUploadModal, createVideoUploadHandler } from '../../modals/video-upload-modal.js';
import { refreshActiveSectionContent } from './section-refresh.js';

const selectedVideoIds = new Set();

function bumpTabCount(key, delta) {
    const badge = document.querySelector(`[data-role="tab-count"][data-count-key="${key}"]`);
    if (!badge) return;
    badge.textContent = String(Math.max(0, (parseInt(badge.textContent, 10) || 0) + delta));
}

function getInspectionId() {
    return document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
}

function isLibraryActive() {
    return document.getElementById('section-content-inner')?.dataset.sectionId === 'library-videos';
}

function openUploadModal() {
    const inspectionId = getInspectionId();
    if (!inspectionId) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const endpoint = `${baseUrl}api/inspection-library-videos`;

    videoUploadModal.open();
    createVideoUploadHandler(
        endpoint,
        async () => {
            await refreshActiveSectionContent();
        },
        { inspection_id: inspectionId }
    );
}

async function deleteFromLibrary(card) {
    const confirmed = await confirmDialog(
        'Delete this video from the library? It will be removed from every section too. This cannot be undone.',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-videos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, id: card.dataset.videoId }),
        });
        const result = await response.json();

        if (result.success) {
            card.remove();
            const grid = document.getElementById('video-library-grid');
            const empty = document.getElementById('video-library-empty');
            if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
            bumpTabCount('library-videos', -1);
            showToast('Video deleted from the library.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not delete video.', 'error');
        }
    } catch (err) {
        console.error('Library video delete failed:', err);
    }
}

function updateBulkDeleteButton() {
    const btn = document.getElementById('delete-selected-library-videos-btn');
    const label = document.getElementById('delete-selected-library-videos-label');
    const selectAll = document.getElementById('video-library-select-all');
    const grid = document.getElementById('video-library-grid');

    if (btn) {
        if (selectedVideoIds.size > 0) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            if (label) label.textContent = `Delete Selected (${selectedVideoIds.size})`;
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

async function deleteSelected() {
    if (selectedVideoIds.size === 0) return;
    const ids = Array.from(selectedVideoIds);

    const confirmed = await confirmDialog(
        ids.length > 1
            ? `${ids.length} videos will be permanently deleted from the library, removed from every section too. Continue?`
            : 'This video will be permanently deleted from the library, removed from every section too. Continue?',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-videos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, ids }),
        });
        const result = await response.json();

        if (result.success) {
            const deletedCount = result.deleted || ids.length;
            ids.forEach((id) => {
                document.querySelector(`.inspection-video-card[data-video-id="${id}"]`)?.remove();
                selectedVideoIds.delete(id);
            });
            updateBulkDeleteButton();
            const grid = document.getElementById('video-library-grid');
            const empty = document.getElementById('video-library-empty');
            if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
            bumpTabCount('library-videos', -deletedCount);
            showToast(result.messages?.[0] || 'Deleted successfully.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not delete video(s).', 'error');
        }
    } catch (err) {
        console.error('Bulk library video delete failed:', err);
    }
}

async function toggleAssignment(checkbox) {
    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-video-toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                inspection_id: inspectionId,
                video_id: checkbox.dataset.videoId,
                section_id: checkbox.dataset.sectionId,
                value: checkbox.checked ? 1 : 0,
            }),
        });
        const result = await response.json();
        if (!result.success) {
            checkbox.checked = !checkbox.checked;
            showToast(result.messages?.[0] || 'Could not save.', 'error');
        }
    } catch (err) {
        checkbox.checked = !checkbox.checked;
        console.error('Failed to toggle video assignment:', err);
    }
}

export function initVideoLibrary() {
    document.addEventListener('click', (e) => {
        if (!isLibraryActive()) return;

        if (e.target.closest('#trigger-video-library-upload')) {
            openUploadModal();
            return;
        }

        if (e.target.closest('#delete-selected-library-videos-btn')) {
            deleteSelected();
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-video-library"]');
        if (deleteBtn) {
            const card = deleteBtn.closest('.inspection-video-card');
            if (card) deleteFromLibrary(card);
        }
    });

    document.addEventListener('change', (e) => {
        if (!isLibraryActive()) return;

        if (e.target.id === 'video-library-select-all') {
            const grid = document.getElementById('video-library-grid');
            const checkboxes = grid ? grid.querySelectorAll('[data-action="select-video"]') : [];
            checkboxes.forEach((cb) => {
                cb.checked = e.target.checked;
                if (e.target.checked) selectedVideoIds.add(cb.dataset.id);
                else selectedVideoIds.delete(cb.dataset.id);
            });
            updateBulkDeleteButton();
            return;
        }

        const selectCheckbox = e.target.closest('[data-action="select-video"]');
        if (selectCheckbox && selectCheckbox.closest('.inspection-video-card')?.dataset.mode === 'library') {
            if (selectCheckbox.checked) selectedVideoIds.add(selectCheckbox.dataset.id);
            else selectedVideoIds.delete(selectCheckbox.dataset.id);
            updateBulkDeleteButton();
            return;
        }

        const checkbox = e.target.closest('[data-action="toggle-video-section"]');
        if (checkbox) toggleAssignment(checkbox);
    });

    // Dispatched on #inspection-section-content itself (doesn't bubble) --
    // listen there directly rather than on document, same as videos.js.
    document.getElementById('inspection-section-content')?.addEventListener('section-content-updated', () => {
        if (!isLibraryActive()) return;
        selectedVideoIds.clear();
        updateBulkDeleteButton();
    });
}
