// /resources/js/utils/inspections/photo-library.js
//
// The Photo Library tab: upload happens here (no section required), and
// each card carries a checkbox per section plus a "Contract" checkbox --
// toggling one fires an immediate AJAX save, same as legacy's
// section_allocation(). Delete here is a real delete (removes the photo
// and every assignment everywhere), unlike a section grid's "remove" which
// only unassigns.

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';
import { createUploadHandler } from '../../modals/upload-modal.js';
import { refreshActiveSectionContent } from './section-refresh.js';

const selectedPictureIds = new Set();

function bumpTabCount(key, delta) {
    const badge = document.querySelector(`[data-role="tab-count"][data-count-key="${key}"]`);
    if (!badge) return;
    badge.textContent = String(Math.max(0, (parseInt(badge.textContent, 10) || 0) + delta));
}

function getInspectionId() {
    return document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
}

function isLibraryActive() {
    return document.getElementById('section-content-inner')?.dataset.sectionId === 'library-photos';
}

function openUploadModal() {
    const inspectionId = getInspectionId();
    if (!inspectionId) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const endpoint = `${baseUrl}api/inspection-library-pictures?inspection_id=${encodeURIComponent(inspectionId)}`;

    createUploadHandler(
        endpoint,
        'images',
        async () => {
            showToast('Photos uploaded to the library.', 'success');
            await refreshActiveSectionContent();
        },
        4,
        false,
        { autoProcess: true }
    );
}

async function deleteFromLibrary(card) {
    const confirmed = await confirmDialog(
        'Delete this photo from the library? It will be removed from every section and Contracts too. This cannot be undone.',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-pictures`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, id: card.dataset.pictureId }),
        });
        const result = await response.json();

        if (result.success) {
            card.remove();
            const grid = document.getElementById('photo-library-grid');
            const empty = document.getElementById('photo-library-empty');
            if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
            bumpTabCount('library-photos', -1);
            showToast('Photo deleted from the library.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not delete photo.', 'error');
        }
    } catch (err) {
        console.error('Library photo delete failed:', err);
    }
}

function updateBulkDeleteButton() {
    const btn = document.getElementById('delete-selected-library-photos-btn');
    const label = document.getElementById('delete-selected-library-photos-label');
    const selectAll = document.getElementById('photo-library-select-all');
    const grid = document.getElementById('photo-library-grid');

    if (btn) {
        if (selectedPictureIds.size > 0) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            if (label) label.textContent = `Delete Selected (${selectedPictureIds.size})`;
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

async function deleteSelected() {
    if (selectedPictureIds.size === 0) return;
    const ids = Array.from(selectedPictureIds);

    const confirmed = await confirmDialog(
        ids.length > 1
            ? `${ids.length} photos will be permanently deleted from the library, removed from every section and Contracts too. Continue?`
            : 'This photo will be permanently deleted from the library, removed from every section and Contracts too. Continue?',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-pictures`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, ids }),
        });
        const result = await response.json();

        if (result.success) {
            const deletedCount = result.deleted || ids.length;
            ids.forEach((id) => {
                document.querySelector(`.inspection-photo-card[data-picture-id="${id}"]`)?.remove();
                selectedPictureIds.delete(id);
            });
            updateBulkDeleteButton();
            const grid = document.getElementById('photo-library-grid');
            const empty = document.getElementById('photo-library-empty');
            if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
            bumpTabCount('library-photos', -deletedCount);
            showToast(result.messages?.[0] || 'Deleted successfully.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not delete photo(s).', 'error');
        }
    } catch (err) {
        console.error('Bulk library photo delete failed:', err);
    }
}

async function toggleAssignment(checkbox) {
    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const isContract = checkbox.dataset.action === 'toggle-picture-contract';

    const payload = {
        inspection_id: inspectionId,
        target: isContract ? 'contract' : 'section',
        picture_id: checkbox.dataset.pictureId,
        value: checkbox.checked ? 1 : 0,
    };
    if (!isContract) payload.section_id = checkbox.dataset.sectionId;

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-picture-toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (!result.success) {
            checkbox.checked = !checkbox.checked;
            showToast(result.messages?.[0] || 'Could not save.', 'error');
        }
    } catch (err) {
        checkbox.checked = !checkbox.checked;
        console.error('Failed to toggle photo assignment:', err);
    }
}

export function initPhotoLibrary() {
    document.addEventListener('click', (e) => {
        if (!isLibraryActive()) return;

        if (e.target.closest('#trigger-photo-library-upload')) {
            openUploadModal();
            return;
        }

        if (e.target.closest('#delete-selected-library-photos-btn')) {
            deleteSelected();
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-picture-library"]');
        if (deleteBtn) {
            const card = deleteBtn.closest('.inspection-photo-card');
            if (card) deleteFromLibrary(card);
        }
    });

    document.addEventListener('change', (e) => {
        if (!isLibraryActive()) return;

        if (e.target.id === 'photo-library-select-all') {
            const grid = document.getElementById('photo-library-grid');
            const checkboxes = grid ? grid.querySelectorAll('[data-action="select-picture"]') : [];
            checkboxes.forEach((cb) => {
                cb.checked = e.target.checked;
                if (e.target.checked) selectedPictureIds.add(cb.dataset.id);
                else selectedPictureIds.delete(cb.dataset.id);
            });
            updateBulkDeleteButton();
            return;
        }

        const selectCheckbox = e.target.closest('[data-action="select-picture"]');
        if (selectCheckbox && selectCheckbox.closest('.inspection-photo-card')?.dataset.mode === 'library') {
            if (selectCheckbox.checked) selectedPictureIds.add(selectCheckbox.dataset.id);
            else selectedPictureIds.delete(selectCheckbox.dataset.id);
            updateBulkDeleteButton();
            return;
        }

        const checkbox = e.target.closest('[data-action="toggle-picture-section"], [data-action="toggle-picture-contract"]');
        if (checkbox) toggleAssignment(checkbox);
    });

    // Dispatched on #inspection-section-content itself (doesn't bubble) --
    // listen there directly rather than on document, same as photos.js.
    document.getElementById('inspection-section-content')?.addEventListener('section-content-updated', () => {
        if (!isLibraryActive()) return;
        selectedPictureIds.clear();
        updateBulkDeleteButton();
    });
}
