// /resources/js/forms/standard-form.js

/**
 * Form for adding/editing a single Standards page. The actual rich-text
 * editor (Quill) is mounted onto #{formId}-editor by the caller -- see
 * modals/standards-modal.js -- since Quill needs a live DOM node to bind
 * to, not a string; the initial content is passed straight through in JS
 * (mountEditor()), not read back off this markup.
 */
export function standardForm({ mode = 'add', encodedId = null, formId = 'standard-form' }) {
    const buttonLabel = mode === 'edit' ? 'Save Changes' : 'Add Page';

    return `
    <form id="${formId}" class="w-full space-y-4 font-sans" novalidate>
        <input type="hidden" name="encoded_id" value="${encodedId || ''}">
        <input type="hidden" name="content" id="${formId}-content-input" value="">

        <div id="${formId}-editor-wrapper" class="rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden">
            <div id="${formId}-editor" style="min-height: 320px;"></div>
        </div>

        <div class="api-message"></div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" id="${formId}-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-10 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}
