// /resources/js/utils/leagues/delete-league.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';

export function initDeleteLeague(tableSelector = '#leagues-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/leagues`, 'League');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-league-btn');
        if (!btn) return;

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;
        if (!encodedId || !row) return;

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete league.', 'error');
                return;
            }
            showToast('League deleted successfully', 'success');
        });
    });
}
