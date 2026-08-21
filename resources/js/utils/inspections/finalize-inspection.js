// /resources/js/utils/inspections/finalize-inspection.js

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';

async function postAction(id, action) {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const response = await fetch(`${baseUrl}api/inspections`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, id }),
    });
    return response.json();
}

/**
 * Wires the detail page's "Finalize Report" / "Reopen for Editing" buttons.
 * Both reload the current route in place afterward since several pieces of
 * the header (badge, access code, button swap) need to change together.
 */
export function initFinalizeInspection() {
    const finalizeBtn = document.getElementById('finalize-inspection-btn');
    if (finalizeBtn) {
        finalizeBtn.addEventListener('click', async () => {
            const confirmed = await confirmDialog(
                'This generates the PDF report and starts a 14-day access-code window. Continue?',
                'Finalize',
                'Cancel',
                'bg-secondary-600 hover:bg-secondary-700'
            );
            if (!confirmed) return;

            const result = await postAction(finalizeBtn.dataset.id, 'finalize');
            if (result.success) {
                showToast(result.messages?.[0] || 'Report finalized.', 'success');
                window.loadPartial(window.location.href, false);
            } else {
                showToast(result.messages?.[0] || 'Could not finalize report.', 'error');
            }
        });
    }

    const reopenBtn = document.getElementById('reopen-inspection-btn');
    if (reopenBtn) {
        reopenBtn.addEventListener('click', async () => {
            const confirmed = await confirmDialog(
                'This clears the report\'s access-code expiry window and returns it to "No Report Yet". Continue?',
                'Reopen',
                'Cancel',
                'bg-amber-600 hover:bg-amber-700'
            );
            if (!confirmed) return;

            const result = await postAction(reopenBtn.dataset.id, 'reopen');
            if (result.success) {
                showToast(result.messages?.[0] || 'Inspection reopened.', 'success');
                window.loadPartial(window.location.href, false);
            } else {
                showToast(result.messages?.[0] || 'Could not reopen inspection.', 'error');
            }
        });
    }
}
