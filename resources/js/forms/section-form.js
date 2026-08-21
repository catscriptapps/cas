// /resources/js/forms/section-form.js

import { SECTION_ICONS, sectionIconSvg } from '../utils/questions/section-icons.js';

/**
 * Shared form renderer for Sections (the tabs a question bank is organized
 * into). Mirrors the structure of userForm()/companyForm().
 */
export function sectionForm({
    mode = 'add',
    name = '',
    icon = 'general',
    buttonLabel = 'Save',
    formId = 'section-form',
    encodedId = null,
}) {
    const idPrefix = mode === 'edit' ? 'section-edit' : 'section';
    const dataEncodedIdAttr = encodedId ? `data-encoded-id="${encodedId}"` : '';

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

    const iconButtons = SECTION_ICONS.map((i) => {
        const isSelected = i.key === icon;
        return `
            <button type="button" data-icon-option="${i.key}" title="${i.label}"
                class="icon-option-btn flex items-center justify-center p-3 rounded-xl border-2 transition-all ${isSelected ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-primary-300'}">
                ${sectionIconSvg(i.key, 'w-5 h-5')}
            </button>
        `;
    }).join('');

    return `
    <form
        id="${formId}"
        class="w-full max-w-md mx-auto space-y-6 p-1 font-sans"
        novalidate
        ${dataEncodedIdAttr}>

        <div>
            <label for="${idPrefix}-name" class="${labelClasses}">Section Name</label>
            <input type="text" required id="${idPrefix}-name" name="name"
                placeholder="e.g. Roof, Electrical, Plumbing" value="${name}" class="${inputClasses}" />
        </div>

        <div class="hidden">
            <label class="${labelClasses}">Icon</label>
            <input type="hidden" name="icon" id="${idPrefix}-icon-value" value="${icon}">
            <div class="grid grid-cols-5 sm:grid-cols-9 gap-2" id="${idPrefix}-icon-picker">
                ${iconButtons}
            </div>
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
