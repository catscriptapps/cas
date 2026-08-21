// /resources/js/forms/user-form.js

/**
 * Modern, sleek shared form renderer for Users
 * Colors: Primary (Orange), Secondary (Navy)
 */
export function userForm({
    mode = 'add',
    firstName = '',
    lastName = '',
    email = '',
    city = '',
    countryId = '',
    regionId = '',
    buttonLabel = 'Save',
    formId = 'users-form',
    countries = [],
    regions = [],
    encodedId = null,
    isActive = true,
    // Set (e.g. "Inspector") when a Company Admin is adding a user -- role
    // is locked server-side regardless (see UsersController::save()), this
    // is purely so the form doesn't silently omit a field the user would
    // otherwise expect to see and set themselves.
    forcedRoleLabel = null
}) {
    const idPrefix = mode === 'edit' ? 'users-edit' : 'users';
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

    const eyeIcon = `
        <button type="button" onclick="const p = this.parentElement.querySelector('input'); p.type = p.type === 'password' ? 'text' : 'password';" 
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary-400 mt-6">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>`;

    return `
    <form
        id="${formId}"
        class="w-full max-w-lg mx-auto space-y-6 p-1 font-sans"
        novalidate
        ${dataEncodedIdAttr}
        data-country-id="${countryId}">

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="${idPrefix}-first-name" class="${labelClasses}">First Name</label>
                    <input type="text" required id="${idPrefix}-first-name" name="firstName"
                        placeholder="John" value="${firstName}" class="${inputClasses}" />
                </div>
                <div>
                    <label for="${idPrefix}-last-name" class="${labelClasses}">Last Name</label>
                    <input type="text" required id="${idPrefix}-last-name" name="lastName"
                        placeholder="Doe" value="${lastName}" class="${inputClasses}" />
                </div>
            </div>
            <div>
                <label for="${idPrefix}-email" class="${labelClasses}">Email Address</label>
                <input type="email" required id="${idPrefix}-email" name="email"
                    placeholder="john@example.com" value="${email}" class="${inputClasses}" />
            </div>

            ${forcedRoleLabel ? `
            <div>
                <label for="${idPrefix}-role" class="${labelClasses}">Role</label>
                <select id="${idPrefix}-role" disabled class="${inputClasses} bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <option>${forcedRoleLabel}</option>
                </select>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5 ml-1">Users you add join your company's roster as Inspectors.</p>
            </div>
            ` : ''}

            ${mode === 'edit' ? `
            <div class="flex items-center space-x-2">
                <label for="${idPrefix}-is-active" class="text-xs font-bold text-gray-600 dark:text-gray-400 cursor-pointer">Current (unchecked = Archived)</label>
                <input type="checkbox" id="${idPrefix}-is-active" name="isActive" value="1" ${isActive ? 'checked' : ''}
                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            </div>
            ` : ''}
        </div>

        ${mode === 'add' ? `
        <div class="grid grid-cols-2 gap-4">
            <div class="relative">
                <label for="${idPrefix}-password" class="${labelClasses}">Password</label>
                <input type="password" required id="${idPrefix}-password" name="password"
                    placeholder="••••••••" class="${inputClasses}" />
                ${eyeIcon}
            </div>
            <div class="relative">
                <label for="${idPrefix}-confirm-password" class="${labelClasses}">Confirm Password</label>
                <input type="password" required id="${idPrefix}-confirm-password" name="confirmPassword"
                    placeholder="••••••••" class="${inputClasses}" />
                ${eyeIcon}
            </div>
        </div>
        ` : ''}

        <div class="p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400 ml-1">Location Details</h3>
            <div class="space-y-4">
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