// /resources/js/utils/standards/list.js

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';
import { openEditStandardModal } from '../../modals/standards-modal.js';

// Click-to-place reorder: same two-click swap idiom as Cover Pages/Slideshow
// (see cover-pages-page.js) -- click a page's move icon to mark it, click
// another page's move icon to drop it there.
let activeReorderId = null;

function updatePageNumbers() {
    const list = document.getElementById('standards-list');
    if (!list) return;
    list.querySelectorAll('[data-encoded-id]').forEach((card, index) => {
        const label = card.querySelector('span.text-primary-500, span.text-primary-400');
        if (label) label.textContent = `Page ${index + 1}`;
    });
}

async function persistStandardsOrder() {
    const list = document.getElementById('standards-list');
    if (!list) return;

    const ids = Array.from(list.querySelectorAll('[data-encoded-id]')).map((card) => card.dataset.encodedId);
    if (ids.length === 0) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/standards`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reorder', ids }),
        });
        const result = await response.json();

        if (result.success) {
            updatePageNumbers();
            showToast('Page order updated!', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not save the new order.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

function handleReorderClick(card) {
    const list = document.getElementById('standards-list');
    if (!list) return;

    const clickedId = card.dataset.encodedId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        card.classList.remove('border-transparent');
        card.classList.add('border-secondary-500');
        return;
    }

    const sourceCard = list.querySelector(`[data-encoded-id="${activeReorderId}"]`);
    activeReorderId = null;
    if (sourceCard) {
        sourceCard.classList.remove('border-secondary-500');
        sourceCard.classList.add('border-transparent');
    }

    if (!sourceCard || sourceCard === card) return;

    const allCards = Array.from(list.querySelectorAll('[data-encoded-id]'));
    const sourceIndex = allCards.indexOf(sourceCard);
    const targetIndex = allCards.indexOf(card);
    if (sourceIndex === targetIndex) return;

    sourceCard.remove();
    if (sourceIndex > targetIndex) {
        card.before(sourceCard);
    } else {
        card.after(sourceCard);
    }

    persistStandardsOrder();
}

async function deleteStandardPage(encodedId) {
    const confirmed = await confirmDialog(
        'This page will be permanently removed. Continue?',
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/standards`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ _method: 'DELETE', id: encodedId }),
        });
        const result = await response.json();

        const list = document.getElementById('standards-list');
        if (result.success) {
            if (list) {
                list.innerHTML = result.fullHtml || '';
                if (!result.fullHtml) {
                    list.innerHTML = `
                        <div class="empty-state-placeholder py-12 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl bg-white dark:bg-gray-900">
                            <p class="text-gray-400 text-sm italic">There are currently no Standards of Practice pages for your company. Use "Add Page" to create one.</p>
                        </div>
                    `;
                }
            }
            const countEl = document.getElementById('standards-count');
            if (countEl && list) {
                const count = list.querySelectorAll('[data-encoded-id]').length;
                countEl.textContent = `${count} page${count === 1 ? '' : 's'}`;
            }
            showToast(result.messages?.[0] || 'Page deleted.', 'success');
        } else {
            showToast(result.messages?.[0] || 'Could not delete page.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

export function initStandardsList() {
    const list = document.getElementById('standards-list');
    if (!list || list._standardsListBound) return;
    list._standardsListBound = true;

    list.addEventListener('click', (e) => {
        const reorderBtn = e.target.closest('[data-action="reorder-page"]');
        if (reorderBtn) {
            e.preventDefault();
            const card = reorderBtn.closest('[data-encoded-id]');
            if (card) handleReorderClick(card);
            return;
        }

        const editBtn = e.target.closest('[data-action="edit-page"]');
        if (editBtn) {
            const card = editBtn.closest('[data-encoded-id]');
            const contentEl = card?.querySelector('.prose');
            openEditStandardModal(editBtn.dataset.id, contentEl?.innerHTML || '');
            return;
        }

        const deleteBtn = e.target.closest('[data-action="delete-page"]');
        if (deleteBtn) {
            deleteStandardPage(deleteBtn.dataset.id);
        }
    });
}
