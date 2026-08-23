// /resources/js/utils/divisions/delete-division.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';

export function initDeleteDivision(tableSelector = '#divisions-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/divisions`, 'Division');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-division-btn');
        if (!btn) return;

        const row = btn.closest('tr[data-encoded-id]');
        const encodedId = row?.dataset.encodedId;
        if (!encodedId || !row) return;

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete division.', 'error');
                return;
            }
            showToast('Division deleted successfully', 'success');
        });
    });
}
