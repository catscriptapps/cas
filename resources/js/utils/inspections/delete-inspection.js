// /resources/js/utils/inspections/delete-inspection.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';

/**
 * Wires the delete flow for both surfaces: the list page's per-row delete
 * button (fades and removes the row in place) and the detail page's header
 * delete button (redirects back to the list once the inspection is gone).
 */
export function initDeleteInspection() {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/inspections`, 'Inspection');

    const tbody = document.getElementById('inspections-tbody');
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-action="delete-inspection"]');
            if (!btn) return;

            const row = btn.closest('tr[data-encoded-id]');
            const encodedId = btn.dataset.id;
            if (!encodedId || !row) return;

            deleteHandler.showConfirmation(encodedId, row, (result) => {
                const success = result === true || result?.success;
                if (!success) return;

                showToast('Inspection deleted', 'success');
                const countEl = document.getElementById('inspections-count');
                if (countEl) countEl.textContent = String(document.querySelectorAll('#inspections-tbody tr[data-encoded-id]').length);
            });
        });
    }

    const headerDeleteBtn = document.getElementById('delete-inspection-header-btn');
    if (headerDeleteBtn) {
        headerDeleteBtn.addEventListener('click', () => {
            const encodedId = headerDeleteBtn.dataset.id;
            if (!encodedId) return;

            deleteHandler.showConfirmation(encodedId, document.createElement('div'), (result) => {
                const success = result === true || result?.success;
                if (!success) return;

                showToast('Inspection deleted', 'success');
                setTimeout(() => window.loadPartial(`${baseUrl}inspections`, true), 400);
            });
        });
    }
}
