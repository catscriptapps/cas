// /resources/js/utils/inspections/contracts.js
//
// The Contracts tab: purely display + reorder + remove-from-contract +
// caption for photos already assigned as contract documents (assignment
// itself happens in the Photo Library -- see photo-library.js). Same
// interaction shape as a section's own photo grid.

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';

let activeReorderId = null;
const descriptionTimers = new Map();

function getInspectionId() {
    return document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
}

function isContractsActive() {
    return document.getElementById('section-content-inner')?.dataset.sectionId === 'contracts';
}

async function unassignFromContract(card) {
    const confirmed = await confirmDialog(
        'Remove this photo from Contracts? It stays in the Photo Library.',
        'Remove',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-picture-toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, target: 'contract', picture_id: card.dataset.pictureId, value: 0 }),
        });
        const result = await response.json();

        if (result.success) {
            card.remove();
            const grid = document.getElementById('contracts-grid');
            const empty = document.getElementById('contracts-empty');
            if (grid && empty && grid.children.length === 0) empty.classList.remove('hidden');
            showToast('Removed from Contracts.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not remove photo.', 'error');
        }
    } catch (err) {
        console.error('Contract unassign failed:', err);
    }
}

function clearReorderHighlight() {
    if (!activeReorderId) return;
    document.querySelector(`.inspection-photo-card[data-picture-id="${activeReorderId}"]`)?.classList.remove('border-secondary-500');
    document.querySelector(`.inspection-photo-card[data-picture-id="${activeReorderId}"]`)?.classList.add('border-transparent');
}

function handleReorderClick(card) {
    const grid = document.getElementById('contracts-grid');
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
    const grid = document.getElementById('contracts-grid');
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('.inspection-photo-card')).map((c) => c.dataset.pictureId);
    if (!ids.length) return;

    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/inspection-library-picture-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, target: 'contract', ids }),
        });
        const result = await response.json();
        if (result.success) showToast('Order updated.', 'success');
    } catch (err) {
        console.error('Failed to reorder contracts:', err);
    }
}

async function saveDescription(input) {
    const card = input.closest('.inspection-photo-card');
    if (!card) return;

    const inspectionId = getInspectionId();
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        await fetch(`${baseUrl}api/inspection-library-picture-caption`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspection_id: inspectionId, target: 'contract', picture_id: card.dataset.pictureId, description: input.value }),
        });
    } catch (err) {
        console.error('Failed to save contract caption:', err);
    }
}

export function initContracts() {
    document.addEventListener('click', (e) => {
        if (!isContractsActive()) return;

        const removeBtn = e.target.closest('[data-action="unassign-picture-contract"]');
        if (removeBtn) {
            const card = removeBtn.closest('.inspection-photo-card');
            if (card) unassignFromContract(card);
            return;
        }

        const reorderBtn = e.target.closest('[data-action="reorder-picture"]');
        if (reorderBtn) {
            const card = reorderBtn.closest('.inspection-photo-card');
            if (card && card.dataset.mode === 'contract') handleReorderClick(card);
        }
    });

    document.addEventListener('input', (e) => {
        if (!isContractsActive()) return;
        if (!e.target.matches('[data-role="picture-description"]')) return;
        const card = e.target.closest('.inspection-photo-card');
        if (card?.dataset.mode !== 'contract') return;
        const key = card.dataset.pictureId;
        clearTimeout(descriptionTimers.get(key));
        descriptionTimers.set(key, setTimeout(() => saveDescription(e.target), 600));
    });
}
