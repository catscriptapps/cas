// /resources/js/pages/sponsorship-page.js

import { Modal } from '../factories/modal-factory.js';
import { showToast } from '../ui/toast.js';
import { confirmDialog } from '../ui/confirm.js';
import { openLightbox } from '../ui/lightbox.js';
import { createUploadHandler } from '../modals/upload-modal.js';

/**
 * Admin add/preview/reorder/delete controls for the Sponsorship page's logo
 * grid (see SponsorsController). Uses the app's existing custom primitives
 * end-to-end rather than anything bespoke: createUploadHandler() for the
 * add flow, openLightbox() for preview (available to every viewer, not just
 * admins -- clicking any logo to see it bigger is a reasonable guest
 * courtesy too), and confirmDialog() before a delete.
 *
 * Adding a sponsor is two calls, not one: the shared uploader only ever
 * posts a raw file with no room for a name field, so uploadImage() saves
 * the file and hands back a filename, then a small follow-up Modal asks
 * for the sponsor's name before the actual DB row gets created.
 *
 * Reorder uses the same click-to-place two-click swap idiom as the
 * Slideshow admin page (see slideshow-page.js) and the Standards of
 * Practice / Cover Pages lists elsewhere in this app.
 */

let activeReorderId = null;

let nameModalInstance = null;

function openNamePrompt(uploadedFile) {
    if (nameModalInstance) nameModalInstance.destroy();

    const assetBase = window.APP_CONFIG?.assetBase || '/';

    nameModalInstance = new Modal({
        id: 'sponsor-name-modal',
        title: 'Name This Sponsor',
        content: `
            <div class="space-y-4 font-sans">
                <div class="flex justify-center p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                    <img src="${assetBase}${uploadedFile.url}" class="max-h-28 max-w-full object-contain">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Sponsor Name</label>
                    <input type="text" id="sponsor-name-input" placeholder="e.g. Thornton Pharmacy" autofocus
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="api-message"></div>
                <div class="flex justify-end pt-2">
                    <button type="button" id="save-sponsor-btn"
                        class="inline-flex items-center gap-2 py-2.5 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
                        Add Sponsor
                    </button>
                </div>
            </div>
        `,
        size: 'sm',
        showFooter: false,
    });

    document.getElementById('save-sponsor-btn')?.addEventListener('click', () => saveSponsor(uploadedFile));
    nameModalInstance.open();
}

async function saveSponsor(uploadedFile) {
    const input = document.getElementById('sponsor-name-input');
    const btn = document.getElementById('save-sponsor-btn');
    const apiMsg = document.querySelector('#sponsor-name-modal .api-message');
    const name = input?.value.trim();

    if (apiMsg) apiMsg.innerHTML = '';
    if (!name) {
        if (apiMsg) apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm">Please enter a name.</div>`;
        return;
    }

    btn.disabled = true;
    const originalLabel = btn.innerHTML;
    btn.innerHTML = 'Saving...';

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/sponsors`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, filename: uploadedFile.filename }),
        });
        const result = await response.json();

        if (result.success) {
            appendSponsorCard(result.sponsor);
            showToast('Sponsor added.', 'success');
            nameModalInstance?.close();
        } else if (apiMsg) {
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm">${result.messages?.[0] || 'Save failed.'}</div>`;
        }
    } catch (err) {
        console.error('Sponsor save error:', err);
        if (apiMsg) apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm">Unexpected error. Please try again.</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalLabel;
    }
}

function appendSponsorCard(sponsor) {
    const grid = document.getElementById('sponsors-grid');
    if (!grid) return;

    document.getElementById('no-sponsors-message')?.remove();

    const assetBase = window.APP_CONFIG?.assetBase || '/';
    const src = `${assetBase}images/sponsors/${sponsor.filename}`;

    const card = document.createElement('div');
    card.className = 'sponsor-card group relative p-6 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col items-center text-center';
    card.dataset.sponsorId = sponsor.encoded_id;

    const controls = document.createElement('div');
    controls.className = 'absolute top-3 right-3 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity z-10';
    controls.innerHTML = `
        <button type="button" data-action="reorder-sponsor" title="Move" aria-label="Move sponsor" class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-700 hover:bg-gray-800 text-white shadow-md">
            <i class="fa-solid fa-arrows-up-down-left-right text-xs"></i>
        </button>
        <button type="button" data-delete-sponsor title="Delete sponsor" aria-label="Delete sponsor" class="h-8 w-8 flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md">
            <i class="fa-solid fa-trash text-xs"></i>
        </button>
    `;

    const previewBtn = document.createElement('button');
    previewBtn.type = 'button';
    previewBtn.dataset.previewSponsor = '';
    previewBtn.dataset.sponsorSrc = src;
    previewBtn.className = 'h-28 w-full flex items-center justify-center mb-4 cursor-zoom-in';

    const img = document.createElement('img');
    img.src = src;
    img.alt = sponsor.name;
    img.className = 'max-h-28 max-w-full object-contain pointer-events-none';
    previewBtn.appendChild(img);

    const caption = document.createElement('p');
    caption.className = 'text-xs font-bold text-gray-500 dark:text-gray-400';
    caption.textContent = sponsor.name;

    card.append(controls, previewBtn, caption);
    grid.appendChild(card);
}

async function persistOrder() {
    const grid = document.getElementById('sponsors-grid');
    if (!grid) return;

    const ids = Array.from(grid.querySelectorAll('.sponsor-card')).map((c) => c.dataset.sponsorId);
    if (!ids.length) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/sponsors`, {
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
        console.error('Sponsor reorder error:', err);
        showToast('Server error. Please try again.', 'error');
    }
}

function handleReorderClick(card) {
    const grid = document.getElementById('sponsors-grid');
    if (!grid) return;

    const clickedId = card.dataset.sponsorId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        card.classList.add('ring-2', 'ring-secondary-500');
        return;
    }

    const sourceCard = grid.querySelector(`[data-sponsor-id="${activeReorderId}"]`);
    activeReorderId = null;
    sourceCard?.classList.remove('ring-2', 'ring-secondary-500');

    if (!sourceCard || sourceCard === card) return;

    const allCards = Array.from(grid.querySelectorAll('.sponsor-card'));
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

function maybeShowEmptyState() {
    const grid = document.getElementById('sponsors-grid');
    if (!grid || grid.children.length > 0 || document.getElementById('no-sponsors-message')) return;

    const msg = document.createElement('p');
    msg.id = 'no-sponsors-message';
    msg.className = 'text-sm text-gray-400 font-medium italic text-center py-10';
    msg.textContent = 'No sponsors yet -- click "Add Sponsor" to add the first one.';
    grid.insertAdjacentElement('afterend', msg);
}

async function handleDeleteSponsor(card) {
    if (!card) return;

    const confirmed = await confirmDialog('This sponsor will be permanently removed. Continue?', 'Delete', 'Cancel', 'bg-red-600 hover:bg-red-700');
    if (!confirmed) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/sponsors-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: card.dataset.sponsorId }),
        });
        const result = await response.json();

        if (result.success) {
            card.remove();
            showToast('Sponsor removed.', 'success');
            maybeShowEmptyState();
        } else {
            showToast(result.messages?.[0] || 'Could not delete sponsor.', 'error');
        }
    } catch (err) {
        console.error('Sponsor delete error:', err);
        showToast('Server error. Please try again.', 'error');
    }
}

function openAddSponsorFlow() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    createUploadHandler(
        `${baseUrl}api/sponsors-upload`,
        'images',
        (uploadedFiles) => {
            const file = uploadedFiles?.[0];
            if (file) openNamePrompt(file);
        },
        1,
        false,
        { single: true, maxFiles: 1 }
    );
}

let listenersAttached = false;

export function init() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const previewBtn = e.target.closest('[data-preview-sponsor]');
        if (previewBtn) {
            e.preventDefault();
            openLightbox(previewBtn.dataset.sponsorSrc);
            return;
        }

        const deleteBtn = e.target.closest('[data-delete-sponsor]');
        if (deleteBtn) {
            e.preventDefault();
            handleDeleteSponsor(deleteBtn.closest('.sponsor-card'));
            return;
        }

        const reorderBtn = e.target.closest('[data-action="reorder-sponsor"]');
        if (reorderBtn) {
            e.preventDefault();
            const card = reorderBtn.closest('.sponsor-card');
            if (card) handleReorderClick(card);
            return;
        }

        if (e.target.closest('#add-sponsor-btn')) {
            e.preventDefault();
            openAddSponsorFlow();
        }
    });

    listenersAttached = true;
}
