// /resources/js/modals/note-hints-modal.js
//
// Shared "hints" picker for every notes-type field on the inspection detail
// page (Inspection Summary, per-section Question Notes, Section Comments).
// One modal instance is destroyed and rebuilt each time it's opened for a
// different (type, section) pool -- same idiom as companies-modal.js.

import { Modal } from '../factories/modal-factory.js';
import { showToast } from '../ui/toast.js';
import { confirmDialog } from '../ui/confirm.js';

const TYPE_LABELS = {
    inspection_summary: 'Inspection Summary Hints',
    question_notes: 'Question Notes Hints',
    section_comment: 'Section Comment Hints',
};

let hintsModal = null;
let activeTextarea = null;
let activeType = null;
let activeSectionId = null;
let activeCanManage = false;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function emptyStateHtml() {
    return `<p class="py-8 text-center text-gray-400 text-xs font-medium">No hints saved yet.${activeCanManage ? ' Add one below.' : ''}</p>`;
}

function hintCardHtml(hint) {
    return `
        <div class="hint-card group flex items-start gap-2 p-3 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50"
            data-hint-id="${hint.id}" data-hint-text="${escapeHtml(hint.text)}">
            <button type="button" data-action="use-hint" title="Insert this hint"
                class="shrink-0 mt-0.5 p-1.5 rounded-md text-secondary-600 dark:text-secondary-400 hover:text-white hover:bg-secondary-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </button>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">${escapeHtml(hint.text)}</p>
                <span class="text-[10px] text-gray-400 dark:text-gray-500">${escapeHtml(hint.created)}</span>
            </div>
            ${activeCanManage ? `
                <button type="button" data-action="delete-hint" title="Delete this hint"
                    class="shrink-0 p-1.5 rounded-md text-gray-300 dark:text-gray-600 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 opacity-0 group-hover:opacity-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            ` : ''}
        </div>
    `;
}

function renderHints(hints) {
    const list = document.getElementById('note-hints-list');
    if (!list) return;
    list.innerHTML = hints.length ? hints.map(hintCardHtml).join('') : emptyStateHtml();
}

function insertHintIntoTarget(text) {
    if (!activeTextarea) return;
    const current = activeTextarea.value.trim();
    activeTextarea.value = current === '' ? text : `${current}\n${text}`;
    activeTextarea.dispatchEvent(new Event('input', { bubbles: true }));
    activeTextarea.focus();
    hintsModal?.close();
}

async function fetchHints() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const params = new URLSearchParams({ type: activeType });
    if (activeSectionId) params.set('section_id', activeSectionId);

    const response = await fetch(`${baseUrl}api/note-hints?${params.toString()}`);
    return response.json();
}

async function refreshHints() {
    const result = await fetchHints();
    const list = document.getElementById('note-hints-list');
    if (!list) return;

    if (result.success) {
        renderHints(result.hints || []);
    } else {
        list.innerHTML = `<p class="py-8 text-center text-red-500 text-xs font-bold">${result.messages?.[0] || 'Could not load hints.'}</p>`;
    }
}

async function addHint(textarea, btn) {
    const text = textarea.value.trim();
    if (!text) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    btn.disabled = true;

    try {
        const response = await fetch(`${baseUrl}api/note-hints-save`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: activeType, section_id: activeSectionId, text }),
        });
        const result = await response.json();

        if (result.success) {
            textarea.value = '';
            await refreshHints();
        } else {
            showToast(result.messages?.[0] || 'Could not save hint.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error('Failed to save hint:', err);
    } finally {
        btn.disabled = textarea.value.trim() === '';
    }
}

async function deleteHint(card) {
    const confirmed = await confirmDialog(
        'This hint will be permanently removed. Continue?',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    try {
        const response = await fetch(`${baseUrl}api/note-hints-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: card.dataset.hintId }),
        });
        const result = await response.json();

        if (result.success) {
            card.remove();
            const list = document.getElementById('note-hints-list');
            if (list && !list.children.length) list.innerHTML = emptyStateHtml();
        } else {
            showToast(result.messages?.[0] || 'Could not delete hint.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error('Failed to delete hint:', err);
    }
}

function modalBodyHtml() {
    return `
        <div id="note-hints-list" class="space-y-2 max-h-80 overflow-y-auto">
            <div class="py-8 text-center text-gray-400 text-sm font-bold">Loading&hellip;</div>
        </div>
        ${activeCanManage ? `
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2">
                <textarea id="new-hint-textarea" rows="2" placeholder="Type a new hint to save for next time..."
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 text-gray-900 dark:text-white text-sm py-2.5 px-3 placeholder:text-gray-400 focus:border-secondary-400 focus:ring-secondary-400 resize-y"></textarea>
                <div class="flex justify-end">
                    <button type="button" id="add-hint-btn" disabled
                        class="inline-flex items-center gap-1.5 rounded-xl bg-secondary-500 hover:bg-secondary-600 text-white px-4 py-2 text-xs font-bold shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-secondary-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add Hint
                    </button>
                </div>
            </div>
        ` : ''}
    `;
}

function wireModal() {
    const modalEl = document.getElementById('note-hints-modal');
    if (!modalEl) return;

    modalEl.addEventListener('click', (e) => {
        const useBtn = e.target.closest('[data-action="use-hint"]');
        if (useBtn) {
            const card = useBtn.closest('[data-hint-id]');
            if (card) insertHintIntoTarget(card.dataset.hintText || '');
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-hint"]');
        if (deleteBtn) {
            const card = deleteBtn.closest('[data-hint-id]');
            if (card) deleteHint(card);
            return;
        }

        const addBtn = e.target.closest('#add-hint-btn');
        if (addBtn && !addBtn.disabled) {
            const textarea = document.getElementById('new-hint-textarea');
            if (textarea) addHint(textarea, addBtn);
        }
    });

    modalEl.addEventListener('input', (e) => {
        if (!e.target.matches('#new-hint-textarea')) return;
        const addBtn = document.getElementById('add-hint-btn');
        if (addBtn) addBtn.disabled = e.target.value.trim() === '';
    });
}

/**
 * @param {{type: string, sectionId: string|null, textarea: HTMLTextAreaElement}} config
 */
export async function openNoteHintsModal({ type, sectionId, textarea }) {
    if (!textarea) return;

    activeType = type;
    activeSectionId = sectionId || null;
    activeTextarea = textarea;

    let result;
    try {
        result = await fetchHints();
    } catch (err) {
        showToast('Could not load hints.', 'error');
        console.error('Failed to load hints:', err);
        return;
    }

    if (!result.success) {
        showToast(result.messages?.[0] || 'Could not load hints.', 'error');
        return;
    }

    activeCanManage = !!result.canManage;

    if (hintsModal) hintsModal.destroy();

    hintsModal = new Modal({
        id: 'note-hints-modal',
        title: TYPE_LABELS[type] || 'Hints',
        content: modalBodyHtml(),
        size: 'md',
        showFooter: false,
    });

    hintsModal.open();
    wireModal();
    renderHints(result.hints || []);
}
