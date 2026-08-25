// /resources/js/pages/incident-reports-page.js

import { initIncidentReportsModal } from '../modals/incident-reports-modal.js';
import { initDeleteIncidentReport } from '../utils/incident-reports/delete-incident-report.js';
import { initViewIncidentReport } from '../utils/incident-reports/view-incident-report.js';
import { initDataTable } from '../components/data-table.js';

export function init() {
    initIncidentReportsModal();
    initDeleteIncidentReport();
    initViewIncidentReport();

    initDataTable({
        tbodyId: 'incidents-tbody',
        countId: 'incidents-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/incident-reports`,
        resourceLabel: 'incident',
        colspan: 5,
        defaultSort: 'date',
        defaultDir: 'desc',
    });
}
