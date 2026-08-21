// /resources/js/forms/property-form.js

/**
 * Modern, sleek shared form renderer for Properties
 * Colors: primary (Primary Focus / Submits), Lime/Navy (Secondary Structural Accents)
 */
export function propertyForm({
    mode = 'add',
    propertyName = '',
    unitNumber = '',
    addressLine1 = '',
    city = '',
    countryId = 39, // Defaulting directly to Canada (ID = 39)
    regionId = 866, // Defaulting to Ontario (ID = 866)
    postalCode = '',
    isActive = true,
    buttonLabel = 'Save',
    formId = 'properties-form',
    countries = [],
    regions = [],
    encodedId = null
}) {
    const idPrefix = mode === 'edit' ? 'properties-edit' : 'properties';
    const dataEncodedIdAttr = encodedId ? `data-encoded-id="${encodedId}"` : '';

    const inputClasses = `
        block w-full rounded-lg 
        border border-gray-300 dark:border-gray-600 
        bg-white dark:bg-gray-900 
        text-gray-900 dark:text-white 
        placeholder:text-gray-400 
        focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 
        sm:text-sm transition-all duration-150 py-1.5 px-3 font-sans
    `.replace(/\s+/g, ' ').trim();

    const disabledClasses = "bg-gray-100 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 cursor-not-allowed border-gray-200 dark:border-gray-700";
    const labelClasses = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-0.5 ml-0.5 font-sans";

    return `
    <form 
        id="${formId}"
        class="w-full max-w-lg mx-auto space-y-3 p-0.5 font-sans animate-in fade-in duration-200"
        novalidate 
        ${dataEncodedIdAttr} 
        data-country-id="${countryId}">

        <div class="p-4 rounded-xl bg-gradient-to-br from-primary-100 to-gray-50 dark:from-gray-900 dark:to-gray-800/40 border border-gray-100 dark:border-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-8">
                    <label for="${idPrefix}-property-name" class="${labelClasses}">Property Name</label>
                    <input type="text" required id="${idPrefix}-property-name" name="propertyName"
                        placeholder="e.g., Victoria Complex" value="${propertyName}" class="${inputClasses}" />
                </div>
                <div class="md:col-span-4">
                    <label for="${idPrefix}-unit-number" class="${labelClasses}">Unit / Suite #</label>
                    <input type="text" id="${idPrefix}-unit-number" name="unitNumber"
                        placeholder="e.g., Suite 404" value="${unitNumber}" class="${inputClasses}" />
                </div>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-1.5">
                <div class="flex items-center space-x-2">
                    <span class="w-1.5 h-3 bg-primary-500 rounded-full"></span>
                    <h3 class="text-[10px] font-black uppercase tracking-[0.15em] text-gray-900 dark:text-gray-400">Location Settings</h3>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label for="${idPrefix}-is-active" class="text-xs font-bold text-gray-600 dark:text-gray-400 cursor-pointer">Active Asset</label>
                    <input type="checkbox" id="${idPrefix}-is-active" name="isActive" value="1" ${isActive ? 'checked' : ''} 
                        class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label for="${idPrefix}-address-line1" class="${labelClasses}">Street Address</label>
                    <input type="text" required id="${idPrefix}-address-line1" name="addressLine1"
                        placeholder="88 Lakeshore Rd" value="${addressLine1}" class="${inputClasses}" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="${idPrefix}-city" class="${labelClasses}">City</label>
                    <input type="text" id="${idPrefix}-city" name="city" required
                        placeholder="Innisfil" value="${city}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-postal-code" class="${labelClasses}">Postal Code</label>
                    <input type="text" id="${idPrefix}-postal-code" name="postalCode" required
                        placeholder="L9S 1A1" value="${postalCode}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-country" class="${labelClasses}">Country</label>
                    <select id="${idPrefix}-country" name="countryId" disabled class="${inputClasses} ${disabledClasses}">
                        ${countries.map(c => `<option value="${c.id}" ${c.id == countryId ? 'selected' : ''}>${c.name}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label for="${idPrefix}-region" class="${labelClasses}">Province</label>
                    <select id="${idPrefix}-region" name="regionId" required class="${inputClasses}">
                        <option value="">Select Province</option>
                        ${regions.map(r => `<option value="${r.id}" ${r.id == regionId ? 'selected' : ''}>${r.name}</option>`).join('')}
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-8 py-2 text-sm font-bold text-white shadow-md shadow-primary-600/10 hover:bg-primary-700 transition-all active:scale-98 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}