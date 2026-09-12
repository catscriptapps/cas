// /resources/js/pages/locations-page.js

import { Modal } from '../factories/modal-factory.js';
import { showToast } from '../ui/toast.js';
import { confirmDialog } from '../ui/confirm.js';
import { createUploadHandler } from '../modals/upload-modal.js';
import { FormValidator } from '../utils/form-validator.js';
import { openLightbox } from '../ui/lightbox.js';

/**
 * Admin add/edit/delete/reorder controls for the Locations page's venues
 * (see VenuesController). The venue's photo is picked via the app's shared
 * drag-drop uploader (same one Sponsorship/Slideshow use), not a plain file
 * input -- see openPhotoPicker(). The rest of the form (name/address/
 * directions/sport) then saves as a separate JSON call referencing whatever
 * filename that upload returned, the same two-step shape Sponsorship uses.
 *
 * The uploader's own modal and this page's venue-form Modal can never both
 * be visible at once -- Modal-factory instances stack at a z-index far
 * above the shared uploader's fixed one -- so openPhotoPicker() hides (not
 * destroys) the form modal while the uploader is open and restores it
 * afterward, preserving whatever the admin had already typed.
 *
 * Reorder uses the same click-to-place idiom as Sponsorship/Slideshow, but
 * scoped independently per sport section -- each [data-sport-grid] only
 * ever swaps within itself, matching how sort_order is scoped server-side.
 *
 * Every venue photo (guest or admin, server-rendered or JS-appended after
 * an add/edit) opens in the app's shared lightbox on click, same as
 * Sponsorship's logos.
 */

let formModalInstance = null;
let activeReorderId = null;
let activeReorderGrid = null;

function buildVenueFormHtml(venue) {
    const isEdit = !!venue;
    const hasImage = isEdit && venue.image;
    const assetBase = window.APP_CONFIG?.assetBase || '/';

    return `
        <form id="venue-form" class="space-y-4 font-sans" novalidate>
            <input type="hidden" name="id" value="${isEdit ? venue.encoded_id : ''}">

            <div>
                <label for="venue-name-input" class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Venue Name</label>
                <input type="text" id="venue-name-input" name="name" required value="${isEdit ? escapeAttr(venue.name) : ''}"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Sport</label>
                <select name="sport" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="ball" ${(!isEdit || venue.sport === 'ball') ? 'selected' : ''}>Ball Hockey</option>
                    <option value="ice" ${(isEdit && venue.sport === 'ice') ? 'selected' : ''}>Ice Hockey</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Address</label>
                <input type="text" name="address" value="${isEdit ? escapeAttr(venue.address || '') : ''}"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Directions <span class="normal-case font-medium text-gray-400">(optional)</span></label>
                <textarea name="directions" rows="3"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-medium text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">${isEdit ? escapeHtml(venue.directions || '') : ''}</textarea>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Photo <span class="normal-case font-medium text-gray-400">(optional)</span></label>
                <input type="hidden" name="filename" id="venue-filename-input" value="">
                <input type="hidden" name="remove_image" id="venue-remove-image-input" value="0">
                <div class="flex items-center gap-3">
                    <div id="venue-photo-preview" class="h-16 w-24 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 flex items-center justify-center overflow-hidden">
                        ${hasImage
                            ? `<img src="${assetBase}${venue.image}" class="h-full w-full object-cover">`
                            : `<span class="text-[9px] font-bold text-gray-400 uppercase tracking-wide">No Photo</span>`
                        }
                    </div>
                    <div class="flex flex-col gap-1.5 items-start">
                        <button type="button" id="choose-venue-photo-btn"
                            class="py-1.5 px-4 rounded-full text-xs font-black uppercase tracking-widest bg-primary-50 text-primary-600 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 transition-colors">
                            ${hasImage ? 'Change Photo' : 'Choose Photo'}
                        </button>
                        <button type="button" id="remove-venue-photo-btn" ${hasImage ? '' : 'hidden'}
                            class="text-[11px] font-bold text-red-500 hover:text-red-600 transition-colors">
                            Remove photo
                        </button>
                    </div>
                </div>
            </div>

            <div class="api-message"></div>
            <div class="flex justify-end pt-2">
                <button type="submit" id="save-venue-btn"
                    class="inline-flex items-center gap-2 py-2.5 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
                    ${isEdit ? 'Save Changes' : 'Add Venue'}
                </button>
            </div>
        </form>
    `;
}

function escapeAttr(str) {
    return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
}
function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function openVenueModal(defaultSport, venue) {
    if (formModalInstance) formModalInstance.destroy();

    formModalInstance = new Modal({
        id: 'venue-form-modal',
        title: venue ? 'Edit Venue' : 'Add Venue',
        content: buildVenueFormHtml(venue),
        size: 'md',
        showFooter: false,
    });

    if (!venue && defaultSport) {
        const select = document.querySelector('#venue-form select[name="sport"]');
        if (select) select.value = defaultSport;
    }

    const form = document.getElementById('venue-form');
    const validator = form ? new FormValidator(form) : null;

    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        if (validator && !validator.validateForEmptyFields(e)) return;
        saveVenue(e.target);
    });

    document.getElementById('choose-venue-photo-btn')?.addEventListener('click', () => {
        openPhotoPicker((file) => {
            const assetBase = window.APP_CONFIG?.assetBase || '/';
            document.getElementById('venue-filename-input').value = file.filename;
            document.getElementById('venue-remove-image-input').value = '0';

            const preview = document.getElementById('venue-photo-preview');
            if (preview) preview.innerHTML = `<img src="${assetBase}${file.url}" class="h-full w-full object-cover">`;

            document.getElementById('choose-venue-photo-btn').textContent = 'Change Photo';
            document.getElementById('remove-venue-photo-btn')?.removeAttribute('hidden');
        });
    });

    document.getElementById('remove-venue-photo-btn')?.addEventListener('click', (e) => {
        document.getElementById('venue-filename-input').value = '';
        document.getElementById('venue-remove-image-input').value = '1';

        const preview = document.getElementById('venue-photo-preview');
        if (preview) preview.innerHTML = `<span class="text-[9px] font-bold text-gray-400 uppercase tracking-wide">No Photo</span>`;

        document.getElementById('choose-venue-photo-btn').textContent = 'Choose Photo';
        e.target.setAttribute('hidden', '');
    });

    formModalInstance.open();
}

/**
 * Opens the shared drag-drop uploader for a single photo, hiding (not
 * destroying) the venue-form modal while it's up so the two never fight
 * over the screen, and restoring it -- with whatever the admin had already
 * typed still intact -- once the uploader closes, whether that's from a
 * completed upload or just backing out.
 */
function openPhotoPicker(onUploaded) {
    formModalInstance?.modal?.classList.add('hidden');
    formModalInstance?.overlay?.classList.add('hidden');

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    createUploadHandler(
        `${baseUrl}api/venues-upload`,
        'images',
        (uploadedFiles) => {
            const file = uploadedFiles?.[0];
            if (file) onUploaded(file);
        },
        1,
        false,
        { single: true, maxFiles: 1 }
    );

    document.getElementById('upload-modal')?.addEventListener('modal:closed', () => {
        formModalInstance?.modal?.classList.remove('hidden');
        formModalInstance?.overlay?.classList.remove('hidden');
    }, { once: true });
}

async function saveVenue(form) {
    const btn = document.getElementById('save-venue-btn');
    const apiMsg = document.querySelector('#venue-form-modal .api-message');
    if (apiMsg) apiMsg.innerHTML = '';

    const originalLabel = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Saving...';

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const fd = new FormData(form);
        const payload = {
            id: fd.get('id') || '',
            name: fd.get('name') || '',
            sport: fd.get('sport') || '',
            address: fd.get('address') || '',
            directions: fd.get('directions') || '',
            filename: fd.get('filename') || '',
            remove_image: fd.get('remove_image') === '1',
        };

        const response = await fetch(`${baseUrl}api/venues`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();

        if (result.success) {
            upsertVenueCard(result.venue);
            showToast(result.messages?.[0] || 'Saved.', 'success');
            formModalInstance?.close();
        } else if (apiMsg) {
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm">${result.messages?.[0] || 'Save failed.'}</div>`;
        }
    } catch (err) {
        console.error('Venue save error:', err);
        if (apiMsg) apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm">Unexpected error. Please try again.</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalLabel;
    }
}

function buildVenueCard(venue) {
    const assetBase = window.APP_CONFIG?.assetBase || '/';

    const card = document.createElement('div');
    card.className = 'venue-card group relative rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden';
    card.dataset.venueId = venue.encoded_id;

    const controls = document.createElement('div');
    controls.className = 'absolute top-3 right-3 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity z-10';
    controls.innerHTML = `
        <button type="button" data-action="reorder-venue" title="Move" aria-label="Move venue" class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-700 hover:bg-gray-800 text-white shadow-md">
            <i class="fa-solid fa-arrows-up-down-left-right text-xs"></i>
        </button>
        <button type="button" data-action="edit-venue" title="Edit" aria-label="Edit venue" class="h-8 w-8 flex items-center justify-center rounded-full bg-slate-600 hover:bg-slate-700 text-white shadow-md">
            <i class="fa-solid fa-pen text-xs"></i>
        </button>
        <button type="button" data-action="delete-venue" title="Delete" aria-label="Delete venue" class="h-8 w-8 flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md">
            <i class="fa-solid fa-trash text-xs"></i>
        </button>
    `;

    let mediaHtml;
    if (venue.image) {
        mediaHtml = `
            <button type="button" data-preview-venue data-venue-src="${assetBase}${venue.image}" class="block w-full h-40 cursor-zoom-in">
                <img src="${assetBase}${venue.image}" alt="" class="w-full h-40 object-cover pointer-events-none">
            </button>`;
    } else {
        mediaHtml = `
            <div class="w-full h-40 flex items-center justify-center bg-gray-50 dark:bg-gray-950 text-gray-300 dark:text-gray-700">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>`;
    }

    const media = document.createElement('div');
    media.innerHTML = mediaHtml;

    const text = document.createElement('div');
    text.className = 'p-6';
    text.dataset.venueText = '';
    text.innerHTML = `
        <h3 class="text-sm font-black text-gray-900 dark:text-white" data-field="name"></h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1" data-field="address"></p>
        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mt-2 leading-relaxed" data-field="directions"></p>
    `;
    text.querySelector('[data-field="name"]').textContent = venue.name;
    text.querySelector('[data-field="address"]').textContent = venue.address || '';
    const directionsEl = text.querySelector('[data-field="directions"]');
    if (venue.directions) {
        directionsEl.textContent = venue.directions;
    } else {
        directionsEl.hidden = true;
    }

    card.append(controls, ...media.children, text);
    return card;
}

function upsertVenueCard(venue) {
    const existing = document.querySelector(`.venue-card[data-venue-id="${venue.encoded_id}"]`);
    const targetGrid = document.querySelector(`[data-sport-grid="${venue.sport}"]`);
    if (!targetGrid) return;

    const newCard = buildVenueCard(venue);

    if (existing) {
        const currentGrid = existing.closest('[data-sport-grid]');
        existing.replaceWith(newCard);
        if (currentGrid && currentGrid !== targetGrid) {
            // Sport changed on edit -- VenuesController put it at the end
            // of its new group's order, so it belongs at the end here too.
            targetGrid.appendChild(newCard);
            checkEmptyState(currentGrid);
        }
    } else {
        targetGrid.appendChild(newCard);
    }

    checkEmptyState(targetGrid);
}

function checkEmptyState(grid) {
    const section = grid.closest('[data-sport-section]');
    if (!section) return;
    const emptyMsg = section.querySelector('.no-venues-message');
    if (grid.children.length > 0) {
        emptyMsg?.remove();
    }
    // (No need to conjure the empty-state message back into existence here
    // -- deleting the last venue in a group is rare enough that a stale
    // "no venues" copy briefly missing until the next reload is an
    // acceptable tradeoff for not duplicating that markup in JS.)
}

async function handleDeleteVenue(card) {
    if (!card) return;

    const confirmed = await confirmDialog('This venue will be permanently removed. Continue?', 'Delete', 'Cancel', 'bg-red-600 hover:bg-red-700');
    if (!confirmed) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/venues-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: card.dataset.venueId }),
        });
        const result = await response.json();

        if (result.success) {
            const grid = card.closest('[data-sport-grid]');
            card.remove();
            if (grid) checkEmptyState(grid);
            showToast(result.messages?.[0] || 'Venue removed.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not delete venue.', 'error');
        }
    } catch (err) {
        console.error('Venue delete error:', err);
        showToast('Server error. Please try again.', 'error');
    }
}

async function persistOrder(grid) {
    const ids = Array.from(grid.querySelectorAll('.venue-card')).map((c) => c.dataset.venueId);
    if (!ids.length) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/venues`, {
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
        console.error('Venue reorder error:', err);
        showToast('Server error. Please try again.', 'error');
    }
}

function handleReorderClick(card) {
    const grid = card.closest('[data-sport-grid]');
    if (!grid) return;

    // A reorder marker set inside a DIFFERENT sport's grid is stale (its
    // source card no longer applies) -- swapping across groups isn't
    // meaningful since sort_order is scoped per sport, so just restart.
    if (activeReorderGrid && activeReorderGrid !== grid) {
        activeReorderGrid.querySelector(`[data-venue-id="${activeReorderId}"]`)?.classList.remove('ring-2', 'ring-secondary-500');
        activeReorderId = null;
        activeReorderGrid = null;
    }

    const clickedId = card.dataset.venueId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        activeReorderGrid = grid;
        card.classList.add('ring-2', 'ring-secondary-500');
        return;
    }

    const sourceCard = grid.querySelector(`[data-venue-id="${activeReorderId}"]`);
    activeReorderId = null;
    activeReorderGrid = null;
    sourceCard?.classList.remove('ring-2', 'ring-secondary-500');

    if (!sourceCard || sourceCard === card) return;

    const allCards = Array.from(grid.querySelectorAll('.venue-card'));
    const sourceIndex = allCards.indexOf(sourceCard);
    const targetIndex = allCards.indexOf(card);
    if (sourceIndex === targetIndex) return;

    sourceCard.remove();
    if (sourceIndex > targetIndex) {
        card.before(sourceCard);
    } else {
        card.after(sourceCard);
    }

    persistOrder(grid);
}

let listenersAttached = false;

export function init() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const previewBtn = e.target.closest('[data-preview-venue]');
        if (previewBtn) {
            e.preventDefault();
            openLightbox(previewBtn.dataset.venueSrc);
            return;
        }

        const addBtn = e.target.closest('[data-add-venue]');
        if (addBtn) {
            e.preventDefault();
            openVenueModal(addBtn.dataset.addVenue, null);
            return;
        }

        const editBtn = e.target.closest('[data-action="edit-venue"]');
        if (editBtn) {
            e.preventDefault();
            const card = editBtn.closest('.venue-card');
            if (!card) return;
            const sport = card.closest('[data-sport-grid]')?.dataset.sportGrid;
            openVenueModal(sport, {
                encoded_id: card.dataset.venueId,
                name: card.querySelector('[data-field="name"]')?.textContent || '',
                address: card.querySelector('[data-field="address"]')?.textContent || '',
                directions: card.querySelector('[data-field="directions"]')?.hidden ? '' : (card.querySelector('[data-field="directions"]')?.textContent || ''),
                sport,
                image: card.querySelector('img')?.getAttribute('src')?.replace(window.APP_CONFIG?.assetBase || '/', '') || null,
            });
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-venue"]');
        if (deleteBtn) {
            e.preventDefault();
            handleDeleteVenue(deleteBtn.closest('.venue-card'));
            return;
        }

        const reorderBtn = e.target.closest('[data-action="reorder-venue"]');
        if (reorderBtn) {
            e.preventDefault();
            const card = reorderBtn.closest('.venue-card');
            if (card) handleReorderClick(card);
        }
    });

    listenersAttached = true;
}
