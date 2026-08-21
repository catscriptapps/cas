// /resources/js/utils/questions/question-form-submit.js

import { FormValidator } from '../form-validator.js';
import { buttonSpinner } from '../spinner-utils.js';

/**
 * Maps form data + the dynamic option/field repeater rows to an API
 * payload for Questions.
 */
function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const options = Array.from(form.querySelectorAll('.option-input'))
        .map((el) => el.value.trim())
        .filter((v) => v !== '');

    const fields = Array.from(form.querySelectorAll('.field-row')).map((row) => ({
        prefix: row.querySelector('.field-prefix-input')?.value.trim() || '',
        suffix: row.querySelector('.field-suffix-input')?.value.trim() || '',
    })).filter((f) => f.prefix !== '' || f.suffix !== '');

    return {
        encoded_id: form.dataset.encodedId || null,
        section_id: form.dataset.sectionId,
        question_title: data.question_title?.trim(),
        answer_mode: data.answer_mode,
        is_condition: form.querySelector('input[name="is_condition"]')?.checked ? 1 : 0,
        options,
        fields,
    };
}

export function handleQuestionFormSubmission(form, mode, modalInstance) {
    if (form._questionFormListenerAttached) return;
    form._questionFormListenerAttached = true;

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
            const response = await fetch(`${baseUrl}api/questions`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const list = document.getElementById('questions-list');
                if (list) {
                    if (mode === 'edit' && payload.encoded_id) {
                        const existingRow = list.querySelector(`[data-question-id="${payload.encoded_id}"]`);
                        if (existingRow) existingRow.outerHTML = result.rowHtml;
                    } else if (result.rowHtml) {
                        list.insertAdjacentHTML('beforeend', result.rowHtml);
                        document.getElementById('questions-empty-state')?.classList.add('hidden');
                    }
                    const countLabel = document.getElementById('questions-count');
                    if (countLabel) countLabel.textContent = String(list.children.length);
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
