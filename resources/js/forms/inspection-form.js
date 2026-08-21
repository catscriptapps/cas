// /resources/js/forms/inspection-form.js

/**
 * Shared form renderer for an Inspection's header (property address) --
 * the only fields captured when creating/editing an inspection; section
 * answers, photos, and the summary are all filled in from the detail page.
 */
export function inspectionForm({
    mode = 'add',
    propertyAddress = '',
    city = '',
    countryId = 39, // Canada
    regionId = 866, // Ontario
    postalCode = '',
    buttonLabel = 'Save',
    formId = 'inspections-form',
    countries = [],
    regions = [],
    encodedId = null,
}) {
    const idPrefix = mode === 'edit' ? 'inspections-edit' : 'inspections';
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
        class="w-full max-w-lg mx-auto space-y-5 p-1 font-sans"
        novalidate
        ${dataEncodedIdAttr}
        data-country-id="${countryId}">

        <div>
            <label for="${idPrefix}-property-address" class="${labelClasses}">Property Address</label>
            <input type="text" required id="${idPrefix}-property-address" name="propertyAddress"
                placeholder="123 Main St" value="${propertyAddress}" class="${inputClasses}" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="${idPrefix}-city" class="${labelClasses}">City</label>
                <input type="text" id="${idPrefix}-city" name="city" required
                    placeholder="Barrie" value="${city}" class="${inputClasses}" />
            </div>
            <div>
                <label for="${idPrefix}-postal-code" class="${labelClasses}">Postal / Zip Code</label>
                <input type="text" id="${idPrefix}-postal-code" name="postalCode"
                    placeholder="L4M 1A1" value="${postalCode}" class="${inputClasses}" />
            </div>
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

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-400 px-10 py-3 text-sm font-bold text-white shadow-lg shadow-primary-500/20 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-400 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}
