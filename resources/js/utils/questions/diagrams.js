// /resources/js/utils/questions/diagrams.js
//
// Section Diagrams panel on the Questions page: a per-section gallery of
// full-page images (wiring schematics, layouts, etc.) that get stitched
// into every finished PDF report right after that section's photos,
// full-bleed like Cover Pages. Mirrors cover-pages-page.js's grid/upload/
// reorder/delete pattern closely, plus a per-tile caption (admin-facing
// organizational note only -- not printed on the PDF).

import { createUploadHandler, uploadModal } from '../../modals/upload-modal.js';
import { registerImagePreview } from '../globals/preview.js';
import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';

const DIAGRAMS_LIMIT = 40;
const selectedIds = new Set();
let activeReorderId = null;
let currentSectionId = null;
const captionTimers = new Map();

function updateBulkDeleteButton() {
    const btn = document.getElementById('delete-selected-section-diagrams-btn');
    const label = document.getElementById('delete-selected-section-diagrams-label');
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

function renderEmptyState(grid) {
    grid.insertAdjacentHTML('beforeend', `
        <div class="empty-state-placeholder col-span-full py-8 flex flex-col items-center justify-center border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-2xl">
            <p class="text-gray-400 text-xs italic">No diagrams for this section yet.</p>
        </div>
    `);
}

/**
 * Fetches and repaints the diagrams grid for a given (encoded) section id --
 * used both on tab switch and after add/delete/reorder settle.
 */
export async function loadDiagramsForSection(encodedSectionId) {
    const grid = document.getElementById('section-diagrams-grid');
    if (!grid || !encodedSectionId) return;

    currentSectionId = encodedSectionId;
    selectedIds.clear();
    activeReorderId = null;
    updateBulkDeleteButton();

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/section-diagrams?section_id=${encodeURIComponent(encodedSectionId)}`);
        const result = await response.json();
        if (!result.success) return;

        // A tab switch that lands mid-flight from a slower earlier request
        // shouldn't clobber the grid with stale data for a since-abandoned section.
        if (currentSectionId !== encodedSectionId) return;

        const countLabel = document.getElementById('section-diagrams-count');
        if (countLabel) countLabel.textContent = String(result.diagrams.length);

        grid.innerHTML = '';

        if (result.diagrams.length === 0) {
            renderEmptyState(grid);
            return;
        }

        result.diagrams.forEach((diagram) => {
            const imgSrc = `${baseUrl}${diagram.image_url}`;
            const caption = diagram.caption ? diagram.caption.replace(/"/g, '&quot;') : '';

            grid.insertAdjacentHTML('beforeend', `
                <div class="relative group aspect-[3/4] rounded-xl overflow-hidden border-4 border-transparent bg-gray-50 dark:bg-gray-900/50 shadow-sm transition-colors"
                    data-diagram-id="${diagram.id}">
                    <label class="absolute top-2 left-2 z-10 flex items-center justify-center w-6 h-6 rounded-md bg-white/80 dark:bg-gray-900/80 border border-gray-300 dark:border-gray-700 cursor-pointer shadow-sm">
                        <input type="checkbox" data-action="select-diagram" data-id="${diagram.id}" class="w-4 h-4 text-secondary-600 rounded focus:ring-secondary-500">
                    </label>

                    <button type="button" data-action="reorder-diagram" data-id="${diagram.id}" title="Click to move this diagram, then click another to place it there"
                        class="reorder-btn absolute top-2 right-9 z-10 flex items-center justify-center w-6 h-6 rounded-md bg-white/80 dark:bg-gray-900/80 border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400 shadow-sm transition-colors hover:bg-secondary-50 hover:text-secondary-600 hover:border-secondary-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </button>

                    <button type="button" data-action="delete-diagram" data-id="${diagram.id}" title="Delete diagram"
                        class="absolute top-2 right-2 z-10 p-1.5 rounded-md bg-black/50 text-white hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <img src="${imgSrc}" data-img-src="${imgSrc}" alt="Section diagram"
                        class="w-full h-full object-cover cursor-pointer">

                    <input type="text" data-role="diagram-caption" placeholder="Add a caption (not printed on the PDF)..." value="${caption}"
                        class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[11px] py-1.5 px-2 placeholder:text-white/60 border-0 focus:ring-2 focus:ring-secondary-400 focus:bg-black/80">
                </div>
            `);
        });

        registerImagePreview();
    } catch (error) {
        console.error('Failed to load section diagrams:', error);
    }
}

async function deleteDiagrams(ids) {
    const isBulk = ids.length > 1;
    const confirmed = await confirmDialog(
        isBulk ? `${ids.length} diagrams will be permanently removed. Continue?` : 'This diagram will be permanently removed. Continue?',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const formData = new FormData();
    ids.forEach((id) => formData.append('ids[]', id));

    try {
        const response = await fetch(`${baseUrl}api/section-diagrams-delete`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Deleted successfully.', 'success');
            if (currentSectionId) loadDiagramsForSection(currentSectionId);
        } else {
            showToast(result.message || 'Could not delete diagram(s).', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

async function persistDiagramOrder() {
    const grid = document.getElementById('section-diagrams-grid');
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('[data-diagram-id]')).map((tile) => tile.dataset.diagramId);
    if (ids.length === 0) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/section-diagrams-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const result = await response.json();

        if (result.success) {
            showToast('Diagram order updated!', 'success');
        } else {
            showToast(result.message || 'Could not save the new order.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

async function saveCaption(id, caption) {
    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        await fetch(`${baseUrl}api/section-diagrams-caption`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, caption }),
        });
    } catch (err) {
        console.error('Failed to save diagram caption:', err);
    }
}

/**
 * Click-to-place reorder: same two-click swap idiom as Cover Pages/Standards/
 * Slideshow -- click a diagram's move icon to mark it, click a second
 * diagram's icon to drop it there.
 */
function handleReorderClick(tile) {
    const grid = document.getElementById('section-diagrams-grid');
    if (!grid) return;

    const clickedId = tile.dataset.diagramId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        tile.classList.remove('border-transparent');
        tile.classList.add('border-secondary-500');
        return;
    }

    const sourceTile = grid.querySelector(`[data-diagram-id="${activeReorderId}"]`);
    activeReorderId = null;
    if (sourceTile) {
        sourceTile.classList.remove('border-secondary-500');
        sourceTile.classList.add('border-transparent');
    }

    if (!sourceTile || sourceTile === tile) return;

    const allTiles = Array.from(grid.querySelectorAll('[data-diagram-id]'));
    const sourceIndex = allTiles.indexOf(sourceTile);
    const targetIndex = allTiles.indexOf(tile);
    if (sourceIndex === targetIndex) return;

    sourceTile.remove();
    if (sourceIndex > targetIndex) {
        tile.before(sourceTile);
    } else {
        tile.after(sourceTile);
    }

    persistDiagramOrder();
}

function initDiagramsGrid() {
    const grid = document.getElementById('section-diagrams-grid');
    if (!grid) return;

    grid.addEventListener('change', (e) => {
        const checkbox = e.target.closest('[data-action="select-diagram"]');
        if (!checkbox) return;
        const id = checkbox.dataset.id;
        if (checkbox.checked) selectedIds.add(id);
        else selectedIds.delete(id);
        updateBulkDeleteButton();
    });

    grid.addEventListener('click', (e) => {
        const reorderBtn = e.target.closest('[data-action="reorder-diagram"]');
        if (reorderBtn) {
            e.preventDefault();
            const tile = reorderBtn.closest('[data-diagram-id]');
            if (tile) handleReorderClick(tile);
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-diagram"]');
        if (deleteBtn) {
            deleteDiagrams([deleteBtn.dataset.id]);
        }
    });

    grid.addEventListener('input', (e) => {
        if (!e.target.matches('[data-role="diagram-caption"]')) return;
        const id = e.target.closest('[data-diagram-id]')?.dataset.diagramId;
        if (!id) return;
        clearTimeout(captionTimers.get(id));
        captionTimers.set(id, setTimeout(() => saveCaption(id, e.target.value), 600));
    });
}

function initDiagramsUpload() {
    const trigger = document.getElementById('trigger-section-diagrams-upload');
    if (!trigger) return;

    trigger.addEventListener('click', async () => {
        if (!currentSectionId) return;
        registerImagePreview();

        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const endpoint = `${baseUrl}api/section-diagrams-upload?section_id=${encodeURIComponent(currentSectionId)}`;

        try {
            const response = await fetch(`${baseUrl}api/section-diagrams?section_id=${encodeURIComponent(currentSectionId)}`);
            const result = await response.json();
            const currentCount = result.success ? result.diagrams.length : 0;
            const remainingSlots = DIAGRAMS_LIMIT - currentCount;

            if (remainingSlots <= 0) {
                showToast(`You have reached the limit of ${DIAGRAMS_LIMIT} diagrams for this section.`, 'error');
                return;
            }

            uploadModal.open();

            // Bind synchronously -- createUploadHandler() attaches the file
            // input's change handler immediately, so a file picked inside a
            // deferred window would fire before any handler exists to catch it.
            createUploadHandler(
                endpoint,
                'images',
                () => {
                    showToast('Diagrams uploaded!', 'success');
                    loadDiagramsForSection(currentSectionId);
                },
                4,
                false,
                { autoProcess: true, skipOptimization: true, maxFiles: remainingSlots }
            );
        } catch (err) {
            showToast('Could not verify current diagram count.', 'error');
        }
    });
}

function initBulkDelete() {
    const btn = document.getElementById('delete-selected-section-diagrams-btn');
    if (!btn) return;

    btn.addEventListener('click', () => {
        if (selectedIds.size === 0) return;
        deleteDiagrams(Array.from(selectedIds));
    });
}

export function initSectionDiagrams() {
    initDiagramsGrid();
    initDiagramsUpload();
    initBulkDelete();
}
