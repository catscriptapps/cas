// /resources/js/pages/cover-pages-page.js

import { AnimationEngine } from '../utils/animations';
import { createUploadHandler, uploadModal } from '../modals/upload-modal.js';
import { registerImagePreview } from '../utils/globals/preview.js';
import { photosEmptyStatePlaceholder } from '../utils/helpers.js';
import { confirmDialog } from '../ui/confirm.js';
import { showToast } from '../ui/toast.js';

const COVER_PAGES_LIMIT = 40;
const FRONT = 1;
const BACK = 2;
const selectedIds = new Set();

// Click-to-place reorder: clicking a photo's reorder icon marks it as the
// one being moved; clicking a second photo's reorder icon drops it there.
// Same two-click swap idiom as Slideshow (see slideshow-page.js), which
// itself mirrors legacy's Rearrange.rearrange_obj().
let activeReorderId = null;

function updateBulkDeleteButton() {
    const btn = document.getElementById('delete-selected-cover-pages-btn');
    const label = document.getElementById('delete-selected-cover-pages-label');
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

async function loadCoverPages() {
    const grid = document.getElementById('cover-pages-wrapper');
    if (!grid) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/cover-pages`);
        const result = await response.json();

        if (!result.success) return;

        const countLabel = document.getElementById('cover-pages-count');
        if (countLabel) countLabel.textContent = String(result.pages.length);

        grid.innerHTML = '';
        selectedIds.clear();
        activeReorderId = null;
        updateBulkDeleteButton();

        if (result.pages.length === 0) {
            photosEmptyStatePlaceholder(grid);
            return;
        }

        result.pages.forEach((page) => {
            const imgSrc = `${baseUrl}${page.image_url}`;
            const isBack = Number(page.page_location) === BACK;

            const html = `
                <div class="relative group aspect-[3/4] rounded-2xl overflow-hidden border-4 border-transparent bg-gray-50 dark:bg-gray-900/50 shadow-sm transition-colors"
                    data-cover-page-id="${page.id}">
                    <label class="absolute top-2 right-2 z-10 flex items-center justify-center w-6 h-6 rounded-md bg-white/80 dark:bg-gray-900/80 border border-gray-300 dark:border-gray-700 cursor-pointer shadow-sm">
                        <input type="checkbox" data-action="select-picture" data-id="${page.id}" class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                    </label>

                    <button type="button" data-action="reorder-picture" data-id="${page.id}" title="Click to move this photo, then click another photo to place it there"
                        class="reorder-btn absolute top-2 left-2 z-10 flex items-center justify-center w-6 h-6 rounded-md bg-white/80 dark:bg-gray-900/80 border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400 shadow-sm transition-colors hover:bg-secondary-50 hover:text-secondary-600 hover:border-secondary-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </button>

                    <img src="${imgSrc}"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 pointer-events-none"
                        alt="Cover Page Image">

                    <div class="absolute inset-0 bg-secondary-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                        <button type="button" data-action="view-picture" data-img-src="${imgSrc}" class="p-2.5 bg-white/20 hover:bg-white/40 rounded-full backdrop-blur-md text-white transition-all transform hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        <button type="button" data-action="delete-picture" data-id="${page.id}" class="p-2.5 bg-red-500/80 hover:bg-red-500 rounded-full text-white transition-all transform hover:scale-110" title="Delete Photo">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                    <select data-action="change-location" data-id="${page.id}"
                        class="absolute bottom-2 left-2 right-2 z-10 text-[11px] font-bold rounded-md border border-gray-300 dark:border-gray-700 bg-white/90 dark:bg-gray-900/90 text-gray-700 dark:text-gray-200 px-1.5 py-1 shadow-sm">
                        <option value="${FRONT}" ${!isBack ? 'selected' : ''}>Front</option>
                        <option value="${BACK}" ${isBack ? 'selected' : ''}>Back</option>
                    </select>
                </div>
            `;
            grid.insertAdjacentHTML('beforeend', html);
        });

        registerImagePreview();
    } catch (error) {
        console.error('Failed to load cover page images:', error);
    }
}

async function deleteCoverPages(ids) {
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
        const response = await fetch(`${baseUrl}api/cover-pages-delete`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Deleted successfully.', 'success');
            loadCoverPages();
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
 * each image's pos_index (shared ordering across both Front and Back).
 */
async function persistCoverPageOrder() {
    const grid = document.getElementById('cover-pages-wrapper');
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('[data-cover-page-id]')).map((tile) => tile.dataset.coverPageId);
    if (ids.length === 0) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/cover-pages-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const result = await response.json();

        if (result.success) {
            showToast('Cover page order updated!', 'success');
        } else {
            showToast(result.message || 'Could not save the new order.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

async function changeCoverPageLocation(id, pageLocation) {
    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/cover-pages-location`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, page_location: pageLocation }),
        });
        const result = await response.json();

        if (result.success) {
            showToast(pageLocation === BACK ? 'Moved to Back cover.' : 'Moved to Front cover.', 'success');
        } else {
            showToast(result.message || 'Could not update the cover page.', 'error');
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
    const grid = document.getElementById('cover-pages-wrapper');
    if (!grid) return;

    const clickedId = tile.dataset.coverPageId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        // border-transparent must come out, not just get overridden -- both
        // are equal-specificity utility classes, so whichever Tailwind
        // happens to emit later in the stylesheet wins regardless of the
        // order they were added to classList.
        tile.classList.remove('border-transparent');
        tile.classList.add('border-secondary-500');
        return;
    }

    const sourceTile = grid.querySelector(`[data-cover-page-id="${activeReorderId}"]`);
    activeReorderId = null;
    if (sourceTile) {
        sourceTile.classList.remove('border-secondary-500');
        sourceTile.classList.add('border-transparent');
    }

    // Clicking the already-active photo's icon again cancels the move.
    if (!sourceTile || sourceTile === tile) return;

    const allTiles = Array.from(grid.querySelectorAll('[data-cover-page-id]'));
    const sourceIndex = allTiles.indexOf(sourceTile);
    const targetIndex = allTiles.indexOf(tile);
    if (sourceIndex === targetIndex) return;

    sourceTile.remove();
    if (sourceIndex > targetIndex) {
        tile.before(sourceTile);
    } else {
        tile.after(sourceTile);
    }

    persistCoverPageOrder();
}

function initCoverPagesGrid() {
    const grid = document.getElementById('cover-pages-wrapper');
    if (!grid) return;

    grid.addEventListener('change', (e) => {
        const checkbox = e.target.closest('[data-action="select-picture"]');
        if (checkbox) {
            const id = checkbox.dataset.id;
            if (checkbox.checked) selectedIds.add(id);
            else selectedIds.delete(id);
            updateBulkDeleteButton();
            return;
        }

        const locationSelect = e.target.closest('[data-action="change-location"]');
        if (locationSelect) {
            changeCoverPageLocation(locationSelect.dataset.id, Number(locationSelect.value));
        }
    });

    grid.addEventListener('click', (e) => {
        const reorderBtn = e.target.closest('[data-action="reorder-picture"]');
        if (reorderBtn) {
            e.preventDefault();
            const tile = reorderBtn.closest('[data-cover-page-id]');
            if (tile) handleReorderClick(tile);
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-picture"]');
        if (deleteBtn) {
            deleteCoverPages([deleteBtn.dataset.id]);
        }
    });
}

function initCoverPagesUpload() {
    const trigger = document.getElementById('trigger-cover-pages-upload');
    if (!trigger) return;

    trigger.addEventListener('click', async () => {
        registerImagePreview();

        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const endpoint = `${baseUrl}api/cover-pages-upload`;

        try {
            const response = await fetch(`${baseUrl}api/cover-pages`);
            const result = await response.json();
            const currentCount = result.success ? result.pages.length : 0;
            const remainingSlots = COVER_PAGES_LIMIT - currentCount;

            if (remainingSlots <= 0) {
                showToast(`You have reached the limit of ${COVER_PAGES_LIMIT} cover page images.`, 'error');
                return;
            }

            uploadModal.open();

            // Bind synchronously -- createUploadHandler() attaches the file
            // input's change handler immediately, so a file picked inside a
            // deferred setTimeout window would fire before any handler is
            // attached and silently do nothing (see photos.js's fix earlier).
            createUploadHandler(
                endpoint,
                'images',
                () => {
                    showToast('Cover pages updated!', 'success');
                    setTimeout(() => window.location.reload(), 800);
                },
                4,
                false,
                { autoProcess: true, skipOptimization: true, maxFiles: remainingSlots }
            );
        } catch (err) {
            showToast('Could not verify current image count.', 'error');
        }
    });
}

function initBulkDelete() {
    const btn = document.getElementById('delete-selected-cover-pages-btn');
    if (!btn) return;

    btn.addEventListener('click', () => {
        if (selectedIds.size === 0) return;
        deleteCoverPages(Array.from(selectedIds));
    });
}

/**
 * Initialize the Cover Pages admin page events
 */
export function init() {
    AnimationEngine.refresh();
    initCoverPagesGrid();
    initCoverPagesUpload();
    initBulkDelete();
    loadCoverPages();
}
