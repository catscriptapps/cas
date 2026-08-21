// /resources/js/utils/companies/company-logo.js

import { uploadModal, createUploadHandler } from '../../modals/upload-modal.js';
import { showToast } from '../../ui/toast.js';
import { createDeleteHandler } from '../../factories/delete-factory.js';
import { registerImagePreview } from '../globals/preview.js';

let currentCompanyEncodedId = null;
let currentCompanyName = '';

/**
 * Reflects a logo (or the lack of one) across both places it's shown: the
 * small circle in the modal header, and the larger managed preview in the
 * "Company Logo" section.
 */
function setLogoDisplay(fullUrl) {
    const headerContainer = document.getElementById('view-company-avatar-container');
    const headerFallback = document.getElementById('view-company-avatar-fallback');
    const headerImg = document.getElementById('view-company-header-logo-img');

    const logoContainer = document.getElementById('view-company-logo-container');
    const bodyFallback = document.getElementById('view-company-logo-fallback');
    const bodyImg = document.getElementById('view-company-logo-img');
    const deleteBtn = document.getElementById('view-company-logo-delete-btn');

    const hasLogo = !!fullUrl;

    if (headerContainer) headerContainer.dataset.imgSrc = hasLogo ? fullUrl : '';
    if (logoContainer) logoContainer.dataset.imgSrc = hasLogo ? fullUrl : '';

    if (headerImg) {
        headerImg.src = hasLogo ? fullUrl : '';
        headerImg.classList.toggle('hidden', !hasLogo);
    }
    if (headerFallback) headerFallback.classList.toggle('hidden', hasLogo);

    if (bodyImg) {
        bodyImg.src = hasLogo ? fullUrl : '';
        bodyImg.classList.toggle('hidden', !hasLogo);
    }
    if (bodyFallback) bodyFallback.classList.toggle('hidden', hasLogo);

    if (deleteBtn) deleteBtn.classList.toggle('hidden', !hasLogo);
}

/**
 * Called from view-company.js each time the detail modal opens for a
 * company, so the upload/delete handlers below know which company they're
 * acting on and the header/body fallback initials are correct.
 */
export function initLogoForCompany(encodedCompanyId, companyName, logoUrl) {
    currentCompanyEncodedId = encodedCompanyId;
    currentCompanyName = companyName || '';

    const initial = currentCompanyName ? currentCompanyName.charAt(0).toUpperCase() : '?';
    const headerFallback = document.getElementById('view-company-avatar-fallback');
    const bodyFallback = document.getElementById('view-company-logo-fallback');
    if (headerFallback) headerFallback.textContent = initial;
    if (bodyFallback) bodyFallback.textContent = initial;

    setLogoDisplay(logoUrl && logoUrl.trim() !== '' ? logoUrl : null);
}

/**
 * Keeps the companies table in sync without a full page/partial reload,
 * which would otherwise close the modal the user is actively working in.
 */
function updateRowLogo(encodedId, fullUrl, companyName) {
    const row = document.querySelector(`tr[data-encoded-id="${encodedId}"]`);
    if (!row) return;

    const trigger = row.querySelector('.view-company-trigger');
    if (trigger) trigger.dataset.logoUrl = fullUrl || '';

    const flexContainer = row.querySelector('td:first-child > div.flex');
    const avatarEl = flexContainer?.firstElementChild;
    if (!avatarEl) return;

    if (fullUrl) {
        const img = document.createElement('img');
        img.className = 'h-10 w-10 flex-shrink-0 rounded-full object-cover border border-gray-200 dark:border-gray-700';
        img.src = fullUrl;
        img.alt = companyName || '';
        avatarEl.replaceWith(img);
    } else {
        const div = document.createElement('div');
        div.className = 'h-10 w-10 flex-shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-lg';
        div.textContent = (companyName || 'C').charAt(0).toUpperCase();
        avatarEl.replaceWith(div);
    }
}

export function initCompanyLogoPanel() {
    registerImagePreview();

    if (window._companyLogoListenersAttached) return;
    window._companyLogoListenersAttached = true;

    document.addEventListener('click', (e) => {
        const uploadBtn = e.target.closest('[data-action="upload-logo"]');
        if (uploadBtn) {
            e.preventDefault();
            if (!currentCompanyEncodedId) return;

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            uploadModal.open();

            // Short delay ensures modal DOM is injected and ready for the handler
            setTimeout(() => {
                createUploadHandler(
                    `${baseUrl}api/company-logo-upload?company_id=${encodeURIComponent(currentCompanyEncodedId)}`,
                    'company-logo',
                    (files) => {
                        const relUrl = files?.[0]?.url;
                        if (!relUrl) return;
                        const fullUrl = `${baseUrl}${relUrl}`;
                        setLogoDisplay(fullUrl);
                        updateRowLogo(currentCompanyEncodedId, fullUrl, currentCompanyName);
                        showToast('✅ Logo updated!', 'success');
                    },
                    1,
                    true,
                    { single: true }
                );
            }, 50);
            return;
        }

        const deleteBtn = e.target.closest('#view-company-logo-delete-btn');
        if (deleteBtn) {
            e.preventDefault();
            if (!currentCompanyEncodedId) return;

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const deleteHandler = createDeleteHandler(`${baseUrl}api/company-logo-delete`, 'Company Logo');

            // The factory's second argument is normally a list row it fades/removes
            // on success -- there's no such row here (this deletes a singleton
            // logo, not a list item), so a detached throwaway element is passed
            // purely to satisfy that contract; all real UI updates happen below.
            deleteHandler.showConfirmation(currentCompanyEncodedId, document.createElement('div'), (result) => {
                const success = result === true || result?.success;
                if (!success) return;

                setLogoDisplay(null);
                updateRowLogo(currentCompanyEncodedId, null, currentCompanyName);
                showToast('🗑️ Logo removed!', 'success');
            });
        }
    });
}
