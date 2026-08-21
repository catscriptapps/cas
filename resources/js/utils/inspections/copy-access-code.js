// /resources/js/utils/inspections/copy-access-code.js
//
// Copy-to-clipboard button next to an access code -- used on both the
// Inspections list table (data-row.php) and the inspection detail header
// (inspections/detail.php).

import { copyToClipboard } from '../clipboard.js';
import { showToast } from '../../ui/toast.js';

export function initCopyAccessCode() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-action="copy-access-code"]');
        if (!btn) return;

        const code = btn.dataset.code || '';
        if (!code) return;

        const succeeded = await copyToClipboard(code);
        showToast(succeeded ? 'Access code copied.' : 'Could not copy access code.', succeeded ? 'success' : 'error');
    });
}
