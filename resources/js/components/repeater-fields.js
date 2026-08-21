// /resources/js/components/repeater-fields.js

import { optionRow, fieldRow } from '../forms/question-form.js';

/**
 * Wires up the "Add Option" / "Add Field" / row-remove buttons in the
 * Question form's two dynamic repeaters.
 */
export function enableQuestionRepeaters(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const optionsList = form.querySelector('[id$="-options-list"]');
    const fieldsList = form.querySelector('[id$="-fields-list"]');

    form.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="add-option-row"]')) {
            e.preventDefault();
            optionsList?.insertAdjacentHTML('beforeend', optionRow());
            return;
        }

        if (e.target.closest('[data-action="add-field-row"]')) {
            e.preventDefault();
            fieldsList?.insertAdjacentHTML('beforeend', fieldRow());
            return;
        }

        const removeOptionBtn = e.target.closest('[data-action="remove-option-row"]');
        if (removeOptionBtn) {
            e.preventDefault();
            removeOptionBtn.closest('.option-row')?.remove();
            return;
        }

        const removeFieldBtn = e.target.closest('[data-action="remove-field-row"]');
        if (removeFieldBtn) {
            e.preventDefault();
            removeFieldBtn.closest('.field-row')?.remove();
        }
    });
}
