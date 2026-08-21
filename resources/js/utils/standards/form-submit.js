// /resources/js/utils/standards/form-submit.js

import { buttonSpinner } from '../../utils/spinner-utils.js';

function updateStandardsCount() {
    const list = document.getElementById('standards-list');
    const countEl = document.getElementById('standards-count');
    if (!list || !countEl) return;

    const count = list.querySelectorAll('[data-encoded-id]').length;
    countEl.textContent = `${count} page${count === 1 ? '' : 's'}`;
}

/**
 * Handles Add/Edit Standards page form submission. `quill` is the live
 * editor instance bound to this form's editor div -- its current HTML is
 * pulled into the hidden content input right before the request goes out,
 * since Quill has no native <form> integration.
 */
export function handleStandardFormSubmission(form, mode, modalInstance, quill) {
    if (form._standardFormListenerAttached) return;
    form._standardFormListenerAttached = true;

    const submitBtn = form.querySelector('button[type="submit"]');
    const apiMsg = form.querySelector('.api-message');
    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const html = quill.root.innerHTML;
        const isEmpty = quill.getText().trim().length === 0;
        if (isEmpty) {
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">Page content cannot be empty.</div>`;
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const formData = new FormData(form);
            const payload = {
                encoded_id: formData.get('encoded_id') || null,
                content: html,
            };
            if (mode === 'edit') payload._method = 'POST';

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/standards`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const result = await response.json();

            if (result.success) {
                const list = document.getElementById('standards-list');
                if (list && result.fullHtml) {
                    // #standards-list itself (not its children) carries the
                    // delegated click listener from initStandardsList(), so
                    // it survives this innerHTML swap with no re-init needed.
                    list.innerHTML = result.fullHtml;
                    updateStandardsCount();
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">
                        ${result.messages?.[0] || 'Saved successfully.'}
                    </div>
                `;

                submitBtn.style.visibility = 'hidden';
                setTimeout(() => {
                    if (modalInstance && typeof modalInstance.close === 'function') modalInstance.close();
                }, 800);
            } else {
                apiMsg.innerHTML = (result.messages || ['Something went wrong.']).map((msg) => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">${msg}</div>
                `).join('');
            }
        } catch (err) {
            console.error('Standards submission error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">Unexpected error.</div>`;
        } finally {
            if (submitBtn.style.visibility !== 'hidden') {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
