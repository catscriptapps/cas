// /resources/js/modals/companies-modal.js

import { Modal } from '../factories/modal-factory.js';
import { companyForm } from '../forms/company-form.js';
import { fetchRegions } from '../api/regions-api.js';
import { fetchCountries } from '../api/countries-api.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { enablePostalCodeFormatting } from '../components/postal-code-formatter.js';
import { handleCompanyFormSubmission } from '../utils/companies/form-submit.js';

let companyModal = null;

/**
 * Initialize form features after the modal opens
 */
function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    handleCompanyFormSubmission(form, mode, modalInstance);
    enableDynamicRegionLoading(formId);
    enablePostalCodeFormatting(formId);
}

// --- Add Company ---
export async function openAddCompanyModal() {
    const countryId = '';
    const [countries, regions] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId)
    ]);

    if (companyModal) companyModal.destroy();

    companyModal = new Modal({
        id: 'add-company-modal',
        title: 'New Company',
        content: companyForm({
            mode: 'add',
            formId: 'companies-add-form',
            buttonLabel: 'Create Company',
            countries,
            regions,
            countryId
        }),
        size: 'lg',
        showFooter: false,
    });

    companyModal.open();
    initFormFeatures('companies-add-form', 'add', companyModal);
}

// --- Edit Company ---
export async function openEditCompanyModal(trigger) {
    const btn = trigger.closest('.edit-company-btn') || trigger;
    if (!btn?.dataset) return;

    const data = btn.dataset;
    const countryId = parseInt(data.countryId || '');

    const [countries, regions] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId)
    ]);

    if (companyModal) companyModal.destroy();

    companyModal = new Modal({
        id: 'edit-company-modal',
        title: 'Edit Company',
        content: companyForm({
            mode: 'edit',
            formId: 'companies-edit-form',
            companyName: data.companyName || '',
            email: data.email || '',
            phone: data.phone || '',
            tollFree: data.tollFree || '',
            website: data.website || '',
            slogan: data.slogan || '',
            address: data.address || '',
            city: data.city || '',
            countryId: countryId,
            regionId: parseInt(data.regionId || 0),
            postalCode: data.postalCode || '',
            generalSummary: data.generalSummary || '',
            isActive: data.isActive === "1",
            countries,
            regions,
            buttonLabel: 'Save Changes',
            encodedId: data.encodedId
        }),
        size: 'lg',
        showFooter: false,
    });

    companyModal.open();
    initFormFeatures('companies-edit-form', 'edit', companyModal);
}

let listenersAttached = false;
export function initCompaniesModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#add-company-btn');
        if (addBtn) {
            e.preventDefault();
            openAddCompanyModal();
            return;
        }

        const editBtn = e.target.closest('.edit-company-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditCompanyModal(editBtn);
        }
    });

    listenersAttached = true;
}
