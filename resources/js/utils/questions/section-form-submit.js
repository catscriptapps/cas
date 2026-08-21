// /resources/js/utils/questions/section-form-submit.js

import { FormValidator } from '../form-validator.js';
import { buttonSpinner } from '../spinner-utils.js';
import { activateSectionTab } from './section-activation.js';

/**
 * Maps form data to an API payload for Sections.
 */
function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    return {
        encoded_id: form.dataset.encodedId || null,
        name: data.name?.trim(),
        icon: data.icon || 'general',
    };
}

export function handleSectionFormSubmission(form, mode, modalInstance) {
    if (form._sectionFormListenerAttached) return;
    form._sectionFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    let apiMsg = form.querySelector('.api-message') || (() => {
        const div = document.createElement('div');
        div.className = 'api-message mt-4 transition-all duration-300';
        form.appendChild(div);
        return div;
    })();

    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/sections`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const tabsWrapper = document.getElementById('section-tabs-wrapper');
                if (tabsWrapper) {
                    if (mode === 'edit' && payload.encoded_id) {
                        const existingTab = tabsWrapper.querySelector(`[data-section-id="${payload.encoded_id}"]`);
                        if (existingTab) {
                            existingTab.outerHTML = result.tabHtml;
                            // Re-select it if it was the active tab -- the
                            // server always renders a freshly-saved tab inactive.
                            const activeId = tabsWrapper.dataset.activeSectionId;
                            if (activeId === payload.encoded_id) activateSectionTab(payload.encoded_id);
                        }
                    } else if (result.tabHtml) {
                        tabsWrapper.insertAdjacentHTML('beforeend', result.tabHtml);
                        // activateSectionTab() unhides #sections-empty-state/#section-content(-header) itself.
                        if (result.sectionId) activateSectionTab(result.sectionId);
                    }
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Saved successfully.'}
                    </div>
                `;

                submitBtn.style.visibility = 'hidden';
                setTimeout(() => modalInstance?.close(), 800);
            } else {
                apiMsg.innerHTML = (result.messages || ['Error']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>
                `).join('');
            }
        } catch (err) {
            console.error('Submission Error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Unexpected error.</div>`;
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
