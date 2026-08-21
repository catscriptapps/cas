// /resources/js/pages/slideshow-page.js

import { AnimationEngine } from '../utils/animations';
import { createUploadHandler, uploadModal } from '../modals/upload-modal.js';
import { registerImagePreview } from '../utils/globals/preview.js';
import { photosEmptyStatePlaceholder } from '../utils/helpers.js';
import { confirmDialog } from '../ui/confirm.js';
import { showToast } from '../ui/toast.js';

const SLIDESHOW_LIMIT = 60;
const selectedIds = new Set();

// Click-to-place reorder: clicking a photo's reorder icon marks it as the
// one being moved; clicking a second photo's reorder icon drops it there.
// Mirrors legacy's Rearrange.rearrange_obj() -- a two-click swap instead of
// a continuous drag, so moving a photo from position 0 to position 200
// doesn't require dragging across an entire long gallery.
let activeReorderId = null;

function updateBulkDeleteButton() {
    const btn = document.getElementById('delete-selected-slides-btn');
    const label = document.getElementById('delete-selected-slides-label');
    if (!btn) return;

    if (selectedIds.size > 0) {
        btn.classList.remove('hidden');
        btn.classList.add('flex');
        if (label) label.textContent = `Delete Selected (${selectedIds.size})`;
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('flex');
    }
}

async function loadSlideshowPictures() {
    const grid = document.getElementById('slideshow-pics-wrapper');
    if (!grid) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/slideshow-pictures`);
        const result = await response.json();

        if (!result.success) return;

        const countLabel = document.getElementById('slideshow-pics-count');
        if (countLabel) countLabel.textContent = String(result.pictures.length);

        grid.innerHTML = '';
        selectedIds.clear();
        activeReorderId = null;
        updateBulkDeleteButton();

        if (result.pictures.length === 0) {
            photosEmptyStatePlaceholder(grid);
            return;
        }

        result.pictures.forEach((pic) => {
            const imgSrc = `${baseUrl}images/home/${pic.image_name}`;

            const html = `
                <div class="relative group aspect-square rounded-2xl overflow-hidden border-4 border-transparent bg-gray-50 dark:bg-gray-900/50 shadow-sm transition-colors"
                    data-slide-id="${pic.id}">
                    <label class="absolute top-2 right-2 z-10 flex items-center justify-center w-6 h-6 rounded-md bg-white/80 dark:bg-gray-900/80 border border-gray-300 dark:border-gray-700 cursor-pointer shadow-sm">
                        <input type="checkbox" data-action="select-picture" data-id="${pic.id}" class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                    </label>

                    <button type="button" data-action="reorder-picture" data-id="${pic.id}" title="Click to move this photo, then click another photo to place it there"
                        class="reorder-btn absolute top-2 left-2 z-10 flex items-center justify-center w-6 h-6 rounded-md bg-white/80 dark:bg-gray-900/80 border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400 shadow-sm transition-colors hover:bg-secondary-50 hover:text-secondary-600 hover:border-secondary-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </button>

                    <img src="${imgSrc}"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 pointer-events-none"
                        alt="Slideshow Image">

                    <div class="absolute inset-0 bg-secondary-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                        <button type="button" data-action="view-picture" data-img-src="${imgSrc}" class="p-2.5 bg-white/20 hover:bg-white/40 rounded-full backdrop-blur-md text-white transition-all transform hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        <button type="button" data-action="delete-picture" data-id="${pic.id}" class="p-2.5 bg-red-500/80 hover:bg-red-500 rounded-full text-white transition-all transform hover:scale-110" title="Delete Photo">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            grid.insertAdjacentHTML('beforeend', html);
        });

        registerImagePreview();
    } catch (error) {
        console.error('Failed to load slideshow images:', error);
    }
}

async function deleteSlideshowImages(ids) {
    const isBulk = ids.length > 1;
    const confirmed = await confirmDialog(
        isBulk ? `${ids.length} images will be permanently removed. Continue?` : 'This image will be permanently removed. Continue?',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const formData = new FormData();
    ids.forEach((id) => formData.append('ids[]', id));

    try {
        const response = await fetch(`${baseUrl}api/slideshow-delete`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Deleted successfully.', 'success');
            // Re-fetch and redraw the grid in place -- no hard reload needed.
            // (The guest-facing hero slideshow this table feeds is server-baked
            // into the page too, but it's gated to logged-out visitors only, so
            // an admin managing this grid never has it on screen to go stale.)
            loadSlideshowPictures();
        } else {
            showToast(result.message || 'Could not delete image(s).', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

/**
 * Persists the grid's current left-to-right, top-to-bottom DOM order as
 * each image's pos_index.
 */
async function persistSlideOrder() {
    const grid = document.getElementById('slideshow-pics-wrapper');
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('[data-slide-id]')).map((tile) => tile.dataset.slideId);
    if (ids.length === 0) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/slideshow-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const result = await response.json();

        if (result.success) {
            showToast('Slideshow order updated!', 'success');
        } else {
            showToast(result.message || 'Could not save the new order.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

/**
 * Handles a click on a photo's reorder icon. First click marks that photo
 * as the one to move (thick orange border). Clicking a second photo's icon
 * detaches the marked photo and reinserts it before/after the second one,
 * based on which was originally further down the grid -- then persists.
 */
function handleReorderClick(tile) {
    const grid = document.getElementById('slideshow-pics-wrapper');
    if (!grid) return;

    const clickedId = tile.dataset.slideId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        // border-transparent must come out, not just get overridden -- both
        // are equal-specificity utility classes, so whichever Tailwind
        // happens to emit later in the stylesheet wins regardless of the
        // order they were added to classList, and border-transparent was
        // reliably winning, silently keeping the highlight invisible.
        tile.classList.remove('border-transparent');
        tile.classList.add('border-secondary-500');
        return;
    }

    const sourceTile = grid.querySelector(`[data-slide-id="${activeReorderId}"]`);
    activeReorderId = null;
    if (sourceTile) {
        sourceTile.classList.remove('border-secondary-500');
        sourceTile.classList.add('border-transparent');
    }

    // Clicking the already-active photo's icon again cancels the move.
    if (!sourceTile || sourceTile === tile) return;

    const allTiles = Array.from(grid.querySelectorAll('[data-slide-id]'));
    const sourceIndex = allTiles.indexOf(sourceTile);
    const targetIndex = allTiles.indexOf(tile);
    if (sourceIndex === targetIndex) return;

    sourceTile.remove();
    if (sourceIndex > targetIndex) {
        tile.before(sourceTile);
    } else {
        tile.after(sourceTile);
    }

    persistSlideOrder();
}

function initSlideshowGrid() {
    const grid = document.getElementById('slideshow-pics-wrapper');
    if (!grid) return;

    grid.addEventListener('change', (e) => {
        const checkbox = e.target.closest('[data-action="select-picture"]');
        if (!checkbox) return;

        const id = checkbox.dataset.id;
        if (checkbox.checked) selectedIds.add(id);
        else selectedIds.delete(id);

        updateBulkDeleteButton();
    });

    grid.addEventListener('click', (e) => {
        const reorderBtn = e.target.closest('[data-action="reorder-picture"]');
        if (reorderBtn) {
            e.preventDefault();
            const tile = reorderBtn.closest('[data-slide-id]');
            if (tile) handleReorderClick(tile);
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-picture"]');
        if (deleteBtn) {
            deleteSlideshowImages([deleteBtn.dataset.id]);
        }
    });
}

function initSlideshowUpload() {
    const trigger = document.getElementById('trigger-slideshow-upload');
    if (!trigger) return;

    trigger.addEventListener('click', async () => {
        registerImagePreview();

        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const endpoint = `${baseUrl}api/slideshow-upload-pics`;

        try {
            const response = await fetch(`${baseUrl}api/slideshow-pictures`);
            const result = await response.json();
            const currentCount = result.success ? result.pictures.length : 0;
            const remainingSlots = SLIDESHOW_LIMIT - currentCount;

            if (remainingSlots <= 0) {
                showToast(`You have reached the limit of ${SLIDESHOW_LIMIT} slideshow images.`, 'error');
                return;
            }

            uploadModal.open();

            setTimeout(() => {
                createUploadHandler(
                    endpoint,
                    'images',
                    () => {
                        showToast('Slideshow updated!', 'success');
                        setTimeout(() => window.location.reload(), 800);
                    },
                    4,
                    false,
                    { autoProcess: true, skipOptimization: true, maxFiles: remainingSlots }
                );
            }, 50);
        } catch (err) {
            showToast('Could not verify current image count.', 'error');
        }
    });
}

function initBulkDelete() {
    const btn = document.getElementById('delete-selected-slides-btn');
    if (!btn) return;

    btn.addEventListener('click', () => {
        if (selectedIds.size === 0) return;
        deleteSlideshowImages(Array.from(selectedIds));
    });
}

/**
 * Initialize the Slideshow admin page events
 */
export function init() {
    AnimationEngine.refresh();
    initSlideshowGrid();
    initSlideshowUpload();
    initBulkDelete();
    loadSlideshowPictures();
}
