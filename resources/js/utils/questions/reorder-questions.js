// /resources/js/utils/questions/reorder-questions.js

import { showToast } from '../../ui/toast.js';

// Click-to-place reorder, same mechanism as the Slideshow and Section tabs:
// click a question's move icon to mark it, click another's to drop it there.
let activeReorderId = null;

async function persistQuestionOrder() {
    const list = document.getElementById('questions-list');
    if (!list) return;

    const ids = Array.from(list.querySelectorAll('[data-question-id]')).map((row) => row.dataset.questionId);
    if (ids.length === 0) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/questions-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const result = await response.json();

        if (result.success) {
            showToast('Question order updated!', 'success');
        } else {
            showToast(result.message || 'Could not save the new order.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

function handleReorderClick(row) {
    const list = document.getElementById('questions-list');
    if (!list) return;

    const clickedId = row.dataset.questionId;

    if (!activeReorderId) {
        activeReorderId = clickedId;
        row.classList.add('ring-4', 'ring-secondary-500');
        return;
    }

    const sourceRow = list.querySelector(`[data-question-id="${activeReorderId}"]`);
    activeReorderId = null;
    if (sourceRow) sourceRow.classList.remove('ring-4', 'ring-secondary-500');

    if (!sourceRow || sourceRow === row) return;

    const allRows = Array.from(list.querySelectorAll('[data-question-id]'));
    const sourceIndex = allRows.indexOf(sourceRow);
    const targetIndex = allRows.indexOf(row);
    if (sourceIndex === targetIndex) return;

    sourceRow.remove();
    if (sourceIndex > targetIndex) {
        row.before(sourceRow);
    } else {
        row.after(sourceRow);
    }

    persistQuestionOrder();
}

export function initReorderQuestions() {
    const list = document.getElementById('questions-list');
    if (!list) return;

    list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="reorder-question"]');
        if (!btn) return;

        e.preventDefault();
        const row = btn.closest('[data-question-id]');
        if (row) handleReorderClick(row);
    });
}
