// /resources/js/forms/question-form.js

const inputClasses = `
    block w-full rounded-xl
    border border-gray-400 dark:border-gray-600
    bg-white dark:bg-gray-900
    text-gray-900 dark:text-white
    placeholder:text-gray-400
    focus:border-primary-400 focus:ring-primary-400
    sm:text-sm transition-all duration-200 py-2.5 px-4
`.replace(/\s+/g, ' ').trim();

const labelClasses = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5 ml-1";

function optionRow(value = '') {
    return `
        <div class="option-row flex items-center gap-2">
            <input type="text" class="option-input flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm py-2 px-3 focus:border-primary-400 focus:ring-primary-400" placeholder="e.g. Satisfactory" value="${value}">
            <button type="button" data-action="remove-option-row" class="shrink-0 p-1.5 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    `;
}

function fieldRow(prefix = '', suffix = '') {
    return `
        <div class="field-row flex items-center gap-2">
            <input type="text" class="field-prefix-input flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm py-2 px-3 focus:border-primary-400 focus:ring-primary-400" placeholder="Prefix, e.g. Approx. age:" value="${prefix}">
            <input type="text" class="field-suffix-input flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm py-2 px-3 focus:border-primary-400 focus:ring-primary-400" placeholder="Suffix, e.g. years" value="${suffix}">
            <button type="button" data-action="remove-field-row" class="shrink-0 p-1.5 text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    `;
}

/**
 * Shared form renderer for Questions -- title, answer mode, condition flag,
 * plus dynamic "Answer Options" and "Fill-in Fields" repeaters.
 */
export function questionForm({
    mode = 'add',
    questionTitle = '',
    answerMode = 0,
    isCondition = false,
    options = [],
    fields = [],
    sectionId = '',
    buttonLabel = 'Save',
    formId = 'question-form',
    encodedId = null,
}) {
    const idPrefix = mode === 'edit' ? 'question-edit' : 'question';
    const dataEncodedIdAttr = encodedId ? `data-encoded-id="${encodedId}"` : '';

    const optionRows = (options.length ? options : []).map((o) => optionRow(o)).join('');
    const fieldRows = (fields.length ? fields : []).map((f) => fieldRow(f.prefix || '', f.suffix || '')).join('');

    return `
    <form
        id="${formId}"
        class="w-full max-w-lg mx-auto space-y-6 p-1 font-sans"
        novalidate
        ${dataEncodedIdAttr}
        data-section-id="${sectionId}">

        <div>
            <label for="${idPrefix}-title" class="${labelClasses}">Question Text</label>
            <input type="text" required id="${idPrefix}-title" name="question_title"
                placeholder="e.g. Roof Covering Material" value="${questionTitle}" class="${inputClasses}" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="${idPrefix}-answer-mode" class="${labelClasses}">Answer Mode</label>
                <select id="${idPrefix}-answer-mode" name="answer_mode" class="${inputClasses}">
                    <option value="0" ${Number(answerMode) === 0 ? 'selected' : ''}>Observe &amp; Report</option>
                    <option value="1" ${Number(answerMode) === 1 ? 'selected' : ''}>Perform Tasks</option>
                </select>
            </div>
            <div class="flex items-end pb-2.5">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_condition" value="1" ${isCondition ? 'checked' : ''}
                        class="w-4 h-4 text-secondary-600 border-gray-300 rounded focus:ring-secondary-500 dark:bg-gray-700 dark:border-gray-600">
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400">This is a condition / limitation</span>
                </label>
            </div>
        </div>

        <div class="p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Answer Options</h3>
                <button type="button" data-action="add-option-row" class="text-xs font-bold text-primary-600 hover:text-primary-700 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Add Option
                </button>
            </div>
            <div id="${idPrefix}-options-list" class="space-y-2">${optionRows}</div>
        </div>

        <div class="p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400">Fill-in Fields</h3>
                <button type="button" data-action="add-field-row" class="text-xs font-bold text-primary-600 hover:text-primary-700 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Add Field
                </button>
            </div>
            <div id="${idPrefix}-fields-list" class="space-y-2">${fieldRows}</div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-400 px-10 py-3 text-sm font-bold text-white shadow-lg shadow-primary-500/20 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-400 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}

export { optionRow, fieldRow };
