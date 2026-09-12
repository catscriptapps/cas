// /resources/js/pages/slideshow-page.js

import { showToast } from '../ui/toast.js';
import { confirmDialog } from '../ui/confirm.js';
import { createUploadHandler } from '../modals/upload-modal.js';

/**
 * Admin add/reorder/delete controls for the home page hero's rotating
 * background images (see SlideshowController). Reorder uses the same
 * click-to-place two-click swap idiom as the Standards of Practice /
 * Cover Pages admin lists elsewhere in this app (see utils/standards/list.js)
 * for consistency, rather than a drag library.
 */

let activeReorderId = null;
const selectedIds = new Set();

function getGrid() {
    return document.getElementById('slideshow-grid');
}

function updateSelectionUI() {
    const btn = document.getElementById('delete-selected-slides-btn');
    const countEl = document.getElementById('selected-slides-count');
    if (!btn || !countEl) return;

    countEl.textContent = String(selectedIds.size);
    btn.hidden = selectedIds.size === 0;
}

function removeCardsFromDom(ids) {
    const grid = getGrid();
    if (!grid) return;

    ids.forEach((id) => {
        grid.querySelector(`[data-encoded-id="${id}"]`)?.remove();
        selectedIds.delete(id);
    });
    updateSelectionUI();
    maybeShowEmptyState();
}

function maybeShowEmptyState() {
    const grid = getGrid();
    if (!grid || grid.children.length > 0 || document.getElementById('no-slides-message')) return;

    const msg = document.createElement('p');
    msg.id = 'no-slides-message';
    msg.className = 'text-sm text-gray-400 font-medium italic text-center py-16 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl';
    msg.textContent = 'No slides yet -- click "Add Images" to add the first one.';
    grid.insertAdjacentElement('afterend', msg);
}

function appendSlideCard(slide) {
    const grid = getGrid();
    if (!grid) return;

    document.getElementById('no-slides-message')?.remove();

    const assetBase = window.APP_CONFIG?.assetBase || '/';

    const card = document.createElement('div');
    card.className = 'slide-card group relative rounded-2xl overflow-hidden border-2 border-transparent bg-white dark:bg-gray-900 shadow-sm aspect-video';
    card.dataset.encodedId = slide.encoded_id;

    const img = document.createElement('img');
    img.src = assetBase + slide.filename;
    img.alt = 'Slide';
    img.className = 'w-full h-full object-cover';

    const label = document.createElement('label');
    label.className = 'absolute top-2 left-2 z-10';
    label.innerHTML = `<input type="checkbox" data-select-slide class="h-5 w-5 rounded border-2 border-white shadow accent-primary-600 cursor-pointer">`;

    const controls = document.createElement('div');
    controls.className = 'absolute inset-x-0 bottom-0 flex items-center justify-end gap-1.5 p-2 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity';
    controls.innerHTML = `
        <button type="button" data-action="reorder-slide" title="Move" class="h-8 w-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white backdrop-blur transition-colors">
            <i class="fa-solid fa-arrows-up-down-left-right text-xs"></i>
        </button>
        <button type="button" data-action="delete-slide" title="Delete" class="h-8 w-8 flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 text-white transition-colors">
            <i class="fa-solid fa-trash text-xs"></i>
        </button>
    `;

    card.append(img, label, controls);
    grid.appendChild(card);
}

async function persistOrder() {
    const grid = getGrid();
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('[data-encoded-id]')).map((c) => c.dataset.encodedId);
    if (!ids.length) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/slideshow`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reorder', ids }),
        });
        const result = await response.json();

        if (result.success) {
            showToast('Order updated.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not save the new order.', 'error');
        }
    } catch (err) {
        console.error('Slideshow reorder error:', err);
        showToast('Server error. Please try again.', 'error');
    }
}

function handleReorderClick(card) {
    const grid = getGrid();
    if (!grid) return;

    const clickedId = card.dataset.encodedId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        card.classList.remove('border-transparent');
        card.classList.add('border-secondary-500');
        return;
    }

    const sourceCard = grid.querySelector(`[data-encoded-id="${activeReorderId}"]`);
    activeReorderId = null;
    if (sourceCard) {
        sourceCard.classList.remove('border-secondary-500');
        sourceCard.classList.add('border-transparent');
    }

    if (!sourceCard || sourceCard === card) return;

    const allCards = Array.from(grid.querySelectorAll('[data-encoded-id]'));
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

async function deleteSlides(ids) {
    if (!ids.length) return;

    const confirmed = await confirmDialog(
        ids.length === 1 ? 'This slide will be permanently removed. Continue?' : `${ids.length} slides will be permanently removed. Continue?`,
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/slideshow-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const result = await response.json();

        if (result.success) {
            removeCardsFromDom(ids);
            showToast(result.messages?.[0] || 'Removed.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not delete.', 'error');
        }
    } catch (err) {
        console.error('Slideshow delete error:', err);
        showToast('Server error. Please try again.', 'error');
    }
}

function openAddSlidesFlow() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    createUploadHandler(
        `${baseUrl}api/slideshow-upload`,
        'images',
        (uploadedFiles) => {
            if (!uploadedFiles?.length) return;
            uploadedFiles.forEach(appendSlideCard);
            showToast(`${uploadedFiles.length} image${uploadedFiles.length === 1 ? '' : 's'} added.`, 'success');
        },
        6,
        false,
        {}
    );
}

let listenersAttached = false;

export function init() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        if (e.target.closest('#add-slides-btn')) {
            e.preventDefault();
            openAddSlidesFlow();
            return;
        }

        if (e.target.closest('#delete-selected-slides-btn')) {
            e.preventDefault();
            deleteSlides(Array.from(selectedIds));
            return;
        }

        const reorderBtn = e.target.closest('[data-action="reorder-slide"]');
        if (reorderBtn) {
            e.preventDefault();
            const card = reorderBtn.closest('[data-encoded-id]');
            if (card) handleReorderClick(card);
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-slide"]');
        if (deleteBtn) {
            e.preventDefault();
            const card = deleteBtn.closest('[data-encoded-id]');
            if (card) deleteSlides([card.dataset.encodedId]);
        }
    });

    document.addEventListener('change', (e) => {
        const checkbox = e.target.closest('[data-select-slide]');
        if (!checkbox) return;

        const card = checkbox.closest('[data-encoded-id]');
        if (!card) return;

        if (checkbox.checked) selectedIds.add(card.dataset.encodedId);
        else selectedIds.delete(card.dataset.encodedId);

        updateSelectionUI();
    });

    listenersAttached = true;
}
