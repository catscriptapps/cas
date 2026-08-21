// /resources/js/forms/faq-form.js

/**
 * Modern, sleek shared FAQ form renderer - PURPLE Edition 💜
 */
export function faqForm({
    mode = 'add',
    question = '',
    answer = '',
    displayOrder = 0,
    statusId = 1,
    buttonLabel = 'Save Entry',
    formId = 'faq-form',
    encodedId = null
}) {
    const idPrefix = mode === 'edit' ? 'faq-edit' : 'faq';
    
    // We keep the data attribute on the form, but also add a hidden input for the POST/PUT body
    const dataEncodedIdAttr = encodedId ? `data-encoded-id="${encodedId}"` : '';

    const inputClasses = `
        block w-full rounded-xl 
        border border-gray-400 dark:border-gray-600 
        bg-white dark:bg-gray-900 
        text-gray-900 dark:text-white 
        placeholder:text-gray-400 
        focus:border-primary-500 focus:ring-primary-500 
        sm:text-sm transition-all duration-200 py-2.5 px-4
    `.replace(/\s+/g, ' ').trim();

    const labelClasses = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5 ml-1";

    return `
    <form 
        id="${formId}" 
        class="w-full max-w-2xl mx-auto space-y-5 p-1 font-sans"
        novalidate 
        ${dataEncodedIdAttr}>

        <input type="hidden" name="encoded_id" value="${encodedId || ''}">

        <div>
            <label for="${idPrefix}-question" class="${labelClasses}">Question</label>
            <input type="text" required id="${idPrefix}-question" name="question"
                placeholder="e.g. How do I access the API documentation?" 
                value="${question.replace(/"/g, '&quot;')}" class="${inputClasses}" />
        </div>

        <div>
            <label for="${idPrefix}-answer" class="${labelClasses}">Answer</label>
            <textarea id="${idPrefix}-answer" name="answer" rows="6" required
                placeholder="Provide a clear, detailed answer..." 
                class="${inputClasses} resize-none text-sm leading-relaxed">${answer}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="${idPrefix}-display-order" class="${labelClasses}">Sort Order</label>
                <input type="number" id="${idPrefix}-display-order" name="display_order"
                    value="${displayOrder}" class="${inputClasses}" />
            </div>
            <div>
                <label for="${idPrefix}-status" class="${labelClasses}">Status</label>
                <select id="${idPrefix}-status" name="status_id" class="${inputClasses} appearance-none cursor-pointer">
                    <option value="1" ${parseInt(statusId) === 1 ? 'selected' : ''}>Active / Visible</option>
                    <option value="2" ${parseInt(statusId) === 2 || parseInt(statusId) === 0 ? 'selected' : ''}>Archived / Hidden</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-10 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}