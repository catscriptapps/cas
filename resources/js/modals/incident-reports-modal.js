// /resources/js/modals/incident-reports-modal.js

import { Modal } from '../factories/modal-factory.js';
import { incidentReportForm } from '../forms/incident-report-form.js';
import { handleIncidentReportFormSubmission } from '../utils/incident-reports/form-submit.js';

let incidentModal = null;

function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    handleIncidentReportFormSubmission(form, mode, modalInstance);
}

async function openAddIncidentModal() {
    if (incidentModal) incidentModal.destroy();

    incidentModal = new Modal({
        id: 'add-incident-modal',
        title: 'New Incident Report',
        content: incidentReportForm({
            mode: 'add',
            formId: 'incident-add-form',
            buttonLabel: 'File Report',
        }),
        size: 'lg',
        showFooter: false,
    });

    incidentModal.open();
    initFormFeatures('incident-add-form', 'add', incidentModal);
}

async function openEditIncidentModal(trigger) {
    if (!trigger?.dataset) return;

    const data = trigger.dataset;

    if (incidentModal) incidentModal.destroy();

    incidentModal = new Modal({
        id: 'edit-incident-modal',
        title: 'Edit Incident Report',
        content: incidentReportForm({
            mode: 'edit',
            formId: 'incident-edit-form',
            encodedId: data.encodedId,
            incidentDate: data.incidentDate,
            incidentTime: data.incidentTime,
            location: data.location,
            teamsInvolved: data.teamsInvolved,
            personsInvolved: data.personsInvolved,
            refInvolved: data.refInvolved,
            timekeeper: data.timekeeper,
            incident: data.incident,
            equipmentWorn: data.equipmentWorn,
            medicalAssistance: data.medicalAssistance,
            managerName: data.managerName,
            managerTime: data.managerTime,
            refereeOutcome: data.refereeOutcome,
            signature: data.signature,
            isActive: data.isActive === '1',
            buttonLabel: 'Save Changes',
        }),
        size: 'lg',
        showFooter: false,
    });

    incidentModal.open();
    initFormFeatures('incident-edit-form', 'edit', incidentModal);
}

let listenersAttached = false;

export function initIncidentReportsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#add-incident-btn');
        if (addBtn) {
            e.preventDefault();
            openAddIncidentModal();
            return;
        }

        const editBtn = e.target.closest('.edit-report-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditIncidentModal(editBtn);
            return;
        }
    });

    listenersAttached = true;
}
