// /resources/js/utils/incident-reports/view-incident-report.js

export function initViewIncidentReport() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-report-trigger');
        if (!trigger) return;

        const data = trigger.dataset;
        const modal = document.getElementById('view-report-modal');
        if (!modal) return;

        const fill = (id, val, fallback = 'N/A') => {
            const el = document.getElementById(id);
            if (!el) return;

            if (val && val !== 'null' && val.trim() !== '') {
                const cleaned = val
                    .replace(/\\"/g, '"')
                    .replace(/\\'/g, "'")
                    .replace(/<br\s*\/?>/gi, '\n')
                    .replace(/\\r\\n|\\n|\\r/g, '\n')
                    .trim();

                if (id === 'view-report-description') {
                    el.innerHTML = cleaned.replace(/\n/g, '<br>');
                } else {
                    el.textContent = cleaned;
                }
            } else {
                el.textContent = fallback;
            }
        };

        fill('view-report-title', `Incident: ${data.location}`);
        fill('view-report-subtitle', `${data.dateFormatted} at ${data.incidentTime}`);

        fill('view-report-teams', data.teamsInvolved);
        fill('view-report-persons', data.personsInvolved);
        fill('view-report-officials', data.refInvolved);
        fill('view-report-timekeeper', data.timekeeper);

        fill('view-report-description', data.incident, 'No detailed description provided.');
        fill('view-report-equipment', data.equipmentWorn, 'No specific equipment noted.');

        fill('view-report-outcome', data.refereeOutcome);
        fill('view-report-medical', data.medicalAssistance, 'No medical assistance required.');

        fill('view-report-manager-name', data.managerName, 'Pending Manager Review');
        fill('view-report-manager-time', data.managerTime ? `Review Time: ${data.managerTime}` : '');

        fill('view-report-signature', data.signature, 'UNSIGNED');

        const statusEl = document.getElementById('view-report-status');
        if (statusEl) {
            const isFiled = data.isActive === '1';
            statusEl.textContent = data.status || (isFiled ? 'Filed' : 'Draft');
            statusEl.className = isFiled
                ? 'px-3 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700 border border-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/30'
                : 'px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
        }

        const editBtn = document.getElementById('view-report-edit-btn');
        if (editBtn) {
            editBtn.onclick = () => {
                modal.classList.add('hidden');
                const row = trigger.closest('tr');
                if (row) {
                    const rowEditBtn = row.querySelector('.edit-report-btn');
                    if (rowEditBtn) rowEditBtn.click();
                }
            };
        }

        modal.classList.remove('hidden');
    });

    document.addEventListener('click', (e) => {
        const isCloseTrigger = e.target.closest('.close-view-modal') || e.target.id === 'close-view-modal-overlay';
        if (isCloseTrigger) {
            const modal = document.getElementById('view-report-modal');
            if (modal) modal.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('view-report-modal');
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        }
    });
}
