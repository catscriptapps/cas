// /resources/js/forms/company-form.js

import { escapeHtml } from '../utils/escape-html.js';

/**
 * Modern, sleek shared form renderer for Companies
 * Colors: Primary (Orange), Secondary (Navy)
 */
export function companyForm({
    mode = 'add',
    companyName = '',
    email = '',
    phone = '',
    tollFree = '',
    website = '',
    slogan = '',
    address = '',
    city = '',
    countryId = '',
    regionId = '',
    postalCode = '',
    generalSummary = '',
    buttonLabel = 'Save',
    formId = 'companies-form',
    countries = [],
    regions = [],
    encodedId = null,
    isActive = true
}) {
    const idPrefix = mode === 'edit' ? 'companies-edit' : 'companies';
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

    return `
    <form
        id="${formId}"
        class="w-full mx-auto space-y-6 p-1 font-sans"
        novalidate
        ${dataEncodedIdAttr}
        data-country-id="${countryId}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

            <div class="space-y-4">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400 ml-1">Basic Details</h3>

                <div>
                    <label for="${idPrefix}-company-name" class="${labelClasses}">Company Name</label>
                    <input type="text" required id="${idPrefix}-company-name" name="companyName"
                        placeholder="Acme Home Inspections" value="${companyName}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-email" class="${labelClasses}">Email Address</label>
                    <input type="email" required id="${idPrefix}-email" name="email"
                        placeholder="contact@company.com" value="${email}" class="${inputClasses}" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="${idPrefix}-phone" class="${labelClasses}">Phone</label>
                        <input type="text" id="${idPrefix}-phone" name="phone"
                            placeholder="(555) 123-4567" value="${phone}" class="${inputClasses}" />
                    </div>
                    <div>
                        <label for="${idPrefix}-toll-free" class="${labelClasses}">Toll-Free</label>
                        <input type="text" id="${idPrefix}-toll-free" name="tollFree"
                            placeholder="1-800-555-0100" value="${tollFree}" class="${inputClasses}" />
                    </div>
                </div>
                <div>
                    <label for="${idPrefix}-website" class="${labelClasses}">Website</label>
                    <input type="text" id="${idPrefix}-website" name="website"
                        placeholder="https://www.company.com" value="${website}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-slogan" class="${labelClasses}">Slogan</label>
                    <input type="text" id="${idPrefix}-slogan" name="slogan"
                        placeholder="Peace of mind, inspected." value="${slogan}" class="${inputClasses}" />
                </div>

                ${mode === 'edit' && window.APP_CONFIG?.isAdmin ? `
                <div class="flex items-center space-x-2">
                    <label for="${idPrefix}-is-active" class="text-xs font-bold text-gray-600 dark:text-gray-400 cursor-pointer">Current (unchecked = Archived)</label>
                    <input type="checkbox" id="${idPrefix}-is-active" name="isActive" value="1" ${isActive ? 'checked' : ''}
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                </div>
                ` : ''}
            </div>

            <div class="p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4 self-start">
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400 ml-1">Location Details</h3>
                <div class="space-y-4">
                    <div>
                        <label for="${idPrefix}-address" class="${labelClasses}">Address</label>
                        <input type="text" id="${idPrefix}-address" name="address"
                            placeholder="123 Main St" value="${address}" class="${inputClasses}" />
                    </div>
                    <div>
                        <label for="${idPrefix}-city" class="${labelClasses}">City</label>
                        <input type="text" id="${idPrefix}-city" name="city" required
                            placeholder="Barrie" value="${city}" class="${inputClasses}" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="${idPrefix}-country" class="${labelClasses}">Country</label>
                            <select id="${idPrefix}-country" name="countryId" required class="${inputClasses}">
                                <option value="">Select Country</option>
                                ${countries.map(c => `<option value="${c.id}" ${c.id == countryId ? 'selected' : ''}>${c.name}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label for="${idPrefix}-region" class="${labelClasses}">Region / State</label>
                            <select id="${idPrefix}-region" name="regionId" required class="${inputClasses}">
                                <option value="">Select Region</option>
                                ${regions.map(r => `<option value="${r.id}" ${r.id == regionId ? 'selected' : ''}>${r.name}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="${idPrefix}-postal-code" class="${labelClasses}" data-postal-code-label>Postal / Zip Code</label>
                        <input type="text" id="${idPrefix}-postal-code" name="postalCode"
                            placeholder="L4M 1A1" value="${postalCode}" class="${inputClasses}" />
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-1.5">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400 ml-1">General Inspections Summary</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                This summary is appended to every inspection report for this company, at the end of the PDF.
            </p>
            <textarea id="${idPrefix}-general-summary" name="generalSummary" rows="4"
                placeholder="e.g. general maintenance advice, disclaimers, or closing notes every client should see..."
                class="${inputClasses}">${escapeHtml(generalSummary)}</textarea>
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
