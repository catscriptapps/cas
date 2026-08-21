// /resources/js/utils/questions/delete-question.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';

/**
 * Attaches delete functionality to the questions list via delegation. The
 * list container persists across section-tab switches (only its children
 * are replaced), so this only needs to be wired once.
 */
export function initDeleteQuestion() {
    const list = document.getElementById('questions-list');
    if (!list) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/questions`, 'Question');

    list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="delete-question"]');
        if (!btn) return;

        const row = btn.closest('[data-question-id]');
        const encodedId = row?.dataset.questionId;
        if (!encodedId || !row) return;

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            const success = result === true || result?.success;
            if (!success) return;

            showToast('Question deleted', 'success');

            const countLabel = document.getElementById('questions-count');
            if (countLabel) countLabel.textContent = String(list.children.length);

            if (list.children.length === 0) {
                document.getElementById('questions-empty-state')?.classList.remove('hidden');
            }
        });
    });
}
