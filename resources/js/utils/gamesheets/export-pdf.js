// /resources/js/utils/gamesheets/export-pdf.js

import { showSpinner, hideSpinner } from '../../ui/spinner.js';
import { showToast } from '../../ui/toast.js';

export function initGamesheetsExport() {
    const masterExportBtn = document.getElementById('export-pdf-btn');
    const container = document.querySelector('[data-season-encoded-id]');
    const base = window.APP_CONFIG?.baseUrl?.replace(/\/+$/, '') ?? '';

    const triggerPrint = (encodedSeasonId, isViewAll = 0, encodedScheduleId = null) => {
        showSpinner();
        showToast(encodedScheduleId ? 'Generating Gamesheet...' : 'Generating Gamesheets...', 'success');

        let streamUrl = `${base}/api/gamesheets-pdf?season_id=${encodeURIComponent(encodedSeasonId)}&view_all=${isViewAll}`;
        if (encodedScheduleId) {
            streamUrl += `&schedule_id=${encodeURIComponent(encodedScheduleId)}`;
        }

        window.open(streamUrl, '_blank', 'noopener');

        // Can't detect when the PDF stream finishes loading, so just clear
        // the spinner after a short delay.
        setTimeout(hideSpinner, 1500);
    };

    if (masterExportBtn) {
        masterExportBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const encodedId = container?.dataset.seasonEncodedId;

            if (!encodedId) {
                showToast('Season context not found.', 'error');
                return;
            }

            const viewAllBtn = document.getElementById('view-all-schedules-btn');
            const isViewAll = viewAllBtn?.classList.contains('hidden') ? 1 : 0;

            triggerPrint(encodedId, isViewAll);
        });
    }

    // Delegated (not a direct listener) so it keeps working after the
    // roster accordion's HTML is injected dynamically.
    document.addEventListener('click', (e) => {
        const sectionBtn = e.target.closest('.print-section-btn');
        if (!sectionBtn) return;

        e.preventDefault();

        const table = sectionBtn.closest('.p-4')?.querySelector('.roster-fixed-table');
        const encodedScheduleId = table?.dataset.gameId;
        const encodedSeasonId = container?.dataset.seasonEncodedId;

        if (!encodedScheduleId || !encodedSeasonId) {
            showToast('Required IDs not found for this section.', 'error');
            return;
        }

        triggerPrint(encodedSeasonId, 0, encodedScheduleId);
    });
}
