// /resources/js/utils/schedules/export-pdf.js

import { showSpinner, hideSpinner } from '../../ui/spinner.js';
import { showToast } from '../../ui/toast.js';

export function initSchedulesExport() {
    const exportBtn = document.getElementById('export-pdf-btn');
    const container = document.querySelector('[data-season-encoded-id]');

    if (!exportBtn) return;

    exportBtn.addEventListener('click', (e) => {
        e.preventDefault();

        const encodedId = container?.dataset.seasonEncodedId;
        if (!encodedId) {
            showToast('Season context not found.', 'error');
            return;
        }

        const viewAllBtn = document.getElementById('view-all-schedules-btn');
        const isViewAll = viewAllBtn?.classList.contains('hidden') ? 1 : 0;

        const originalHtml = exportBtn.innerHTML;
        exportBtn.disabled = true;
        showSpinner();

        try {
            const base = window.APP_CONFIG?.baseUrl?.replace(/\/+$/, '') ?? '';
            showToast('Generating PDF...', 'success');

            const streamUrl = `${base}/api/schedules-pdf?season_id=${encodeURIComponent(encodedId)}&view_all=${isViewAll}`;

            window.open(streamUrl, '_blank', 'noopener');

        } catch (err) {
            console.error(err);
            showToast('Failed to generate PDF', 'error');
        } finally {
            setTimeout(() => {
                exportBtn.disabled = false;
                exportBtn.innerHTML = originalHtml;
                hideSpinner();
            }, 1000);
        }
    });
}
