// /resources/js/forms/contact-form.js

import { fetchContactRoles } from '../api/contacts-api.js';

/**
 * Shared form renderer for the Contact Directory. Async because the role
 * dropdown is populated from a real API call (see contacts-api.js) rather
 * than legacy cas-sports' pattern of scraping a hidden <select> already in
 * the DOM.
 */
export async function contactForm({
    mode = 'add',
    fullName = '',
    organization = '',
    email = '',
    phone = '',
    leagues = 'All',
    roleId = 0,
    isEmergency = false,
    buttonLabel = 'Save',
    formId = 'contacts-form',
    encodedId = null
}) {
    const idPrefix = mode === 'edit' ? 'contacts-edit' : 'contacts';
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

    const roles = await fetchContactRoles();

    return `
    <form
        id="${formId}"
        class="w-full max-w-5xl mx-auto space-y-6 p-1 font-sans"
        novalidate
        ${dataEncodedIdAttr}>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
                <label for="${idPrefix}-full-name" class="${labelClasses}">Full Name</label>
                <input type="text" required id="${idPrefix}-full-name" name="full_name"
                    placeholder="John Doe" value="${fullName}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-organization" class="${labelClasses}">Organization</label>
                <input type="text" id="${idPrefix}-organization" name="organization"
                    placeholder="Ref Association" value="${organization}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-email" class="${labelClasses}">Email Address</label>
                <input type="email" id="${idPrefix}-email" name="email"
                    placeholder="john@example.com" value="${email}" class="${inputClasses}" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
                <label for="${idPrefix}-phone" class="${labelClasses}">Phone</label>
                <input type="text" id="${idPrefix}-phone" name="phone"
                    placeholder="705-555-0123" value="${phone}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-5">
                <label for="${idPrefix}-leagues" class="${labelClasses}">Assigned Leagues</label>
                <input type="text" id="${idPrefix}-leagues" name="leagues"
                    placeholder="All, Men's C, Women's Elite" value="${leagues}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-role" class="${labelClasses}">Official Role</label>
                <select id="${idPrefix}-role" name="role_id" required class="${inputClasses} cursor-pointer">
                    ${roles.map(r => `<option value="${r.id}" ${r.id == roleId ? 'selected' : ''}>${r.name}</option>`).join('')}
                </select>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <label class="relative inline-flex items-center cursor-pointer group">
                <input type="checkbox" name="is_emergency" class="sr-only peer" ${isEmergency ? 'checked' : ''}>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-500"></div>
                <div class="ml-3">
                    <span class="block text-sm font-bold text-gray-700 dark:text-gray-300">Emergency Contact</span>
                    <span class="block text-xs text-gray-500">Flags as high-priority on dashboard</span>
                </div>
            </label>

            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-10 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}
