// /resources/js/utils/sports/delete-sport.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';

export function initDeleteSport(tableSelector = '#sports-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/sports`, 'Sport');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-sport-btn');
        if (!btn) return;

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;
        if (!encodedId || !row) return;

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete sport.', 'error');
                return;
            }
            showToast('Sport deleted successfully', 'success');
        });
    });
}
