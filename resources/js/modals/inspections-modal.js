// /resources/js/modals/inspections-modal.js

import { Modal } from '../factories/modal-factory.js';
import { inspectionForm } from '../forms/inspection-form.js';
import { fetchRegions } from '../api/regions-api.js';
import { fetchCountries } from '../api/countries-api.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { handleInspectionFormSubmission } from '../utils/inspections/form-submit.js';

let inspectionModal = null;

function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    handleInspectionFormSubmission(form, mode, modalInstance);
    enableDynamicRegionLoading(formId);
}

export async function openAddInspectionModal() {
    const countryId = 39;
    const [countries, regions] = await Promise.all([fetchCountries(), fetchRegions(countryId)]);

    if (inspectionModal) inspectionModal.destroy();

    inspectionModal = new Modal({
        id: 'add-inspection-modal',
        title: 'New Inspection',
        content: inspectionForm({
            mode: 'add',
            formId: 'inspections-add-form',
            buttonLabel: 'Create Inspection',
            countries,
            regions,
            countryId,
        }),
        size: 'lg',
        showFooter: false,
    });

    inspectionModal.open();
    initFormFeatures('inspections-add-form', 'add', inspectionModal);
}

export async function openEditInspectionModal(trigger) {
    const data = trigger.dataset;
    const countryId = parseInt(data.countryId || '39');

    const [countries, regions] = await Promise.all([fetchCountries(), fetchRegions(countryId)]);

    if (inspectionModal) inspectionModal.destroy();

    inspectionModal = new Modal({
        id: 'edit-inspection-modal',
        title: 'Edit Inspection Header',
        content: inspectionForm({
            mode: 'edit',
            formId: 'inspections-edit-form',
            propertyAddress: data.propertyAddress || '',
            city: data.city || '',
            countryId,
            regionId: parseInt(data.regionId || 0),
            postalCode: data.postalCode || '',
            countries,
            regions,
            buttonLabel: 'Save Changes',
            encodedId: data.encodedId,
        }),
        size: 'lg',
        showFooter: false,
    });

    inspectionModal.open();
    initFormFeatures('inspections-edit-form', 'edit', inspectionModal);
}

let listenersAttached = false;
export function initInspectionsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        if (e.target.closest('#add-inspection-btn')) {
            e.preventDefault();
            openAddInspectionModal();
            return;
        }

        const editBtn = e.target.closest('.edit-inspection-btn, #edit-inspection-header-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditInspectionModal(editBtn);
        }
    });

    listenersAttached = true;
}
