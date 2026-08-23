// /resources/js/forms/registration-form.js

/**
 * Admin edit form for a registration. There's no "add" mode -- registrations
 * only ever originate from the public /register wizard.
 */
export function registrationForm({
    formId = 'registrations-edit-form',
    firstName = '',
    lastName = '',
    email = '',
    phone = '',
    city = '',
    divisionName = '',
    desiredPosition = '',
    teamName = '',
    isActive = true,
    hasPaid = false,
    amountPaid = 0,
    encodedId = null,
}) {
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
    <form id="${formId}" class="w-full max-w-lg mx-auto space-y-6 p-1 font-sans" novalidate
        data-encoded-id="${encodedId ?? ''}">

        <div class="p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-1">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-secondary-400 ml-1 mb-2">Division</h3>
            <p class="text-sm font-bold text-gray-900 dark:text-white ml-1">${divisionName || 'N/A'}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="reg-first-name" class="${labelClasses}">First Name</label>
                <input type="text" required id="reg-first-name" name="firstName" value="${firstName}" class="${inputClasses}" />
            </div>
            <div>
                <label for="reg-last-name" class="${labelClasses}">Last Name</label>
                <input type="text" required id="reg-last-name" name="lastName" value="${lastName}" class="${inputClasses}" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="reg-email" class="${labelClasses}">Email Address</label>
                <input type="email" required id="reg-email" name="email" value="${email}" class="${inputClasses}" />
            </div>
            <div>
                <label for="reg-phone" class="${labelClasses}">Phone</label>
                <input type="text" id="reg-phone" name="phone" value="${phone}" class="${inputClasses}" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="reg-city" class="${labelClasses}">City</label>
                <input type="text" id="reg-city" name="city" value="${city}" class="${inputClasses}" />
            </div>
            <div>
                <label for="reg-position" class="${labelClasses}">Desired Position</label>
                <input type="text" id="reg-position" name="desiredPosition" value="${desiredPosition}" class="${inputClasses}" />
            </div>
        </div>

        <div>
            <label for="reg-team" class="${labelClasses}">Team (if requested)</label>
            <input type="text" id="reg-team" name="teamName" value="${teamName ?? ''}" class="${inputClasses}" />
        </div>

        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-wrap gap-6 items-center justify-between">
            <div class="flex gap-6">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="hasPaid" class="sr-only peer" ${hasPaid ? 'checked' : ''}>
                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-secondary-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    <span class="ml-3 text-sm font-bold text-gray-700 dark:text-gray-300">Payment Received</span>
                </label>
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="isActive" class="sr-only peer" ${isActive ? 'checked' : ''}>
                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-primary-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    <span class="ml-3 text-sm font-bold text-gray-700 dark:text-gray-300">Active Record</span>
                </label>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Amount Paid</span>
                <span class="w-24 text-right font-mono text-sm font-bold text-gray-900 dark:text-white">$${parseFloat(amountPaid || 0).toFixed(2)}</span>
            </div>
        </div>
        <p class="text-[11px] text-gray-400 dark:text-gray-500 -mt-4 ml-1">
            Toggling "Payment Received" on without a PayPal transaction on file (e.g. e-transfer or cash) sets the amount paid to this division's price. Toggling it off clears the recorded payment.
        </p>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" id="registrations-edit-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-500 px-10 py-3 text-sm font-bold text-white shadow-lg shadow-primary-500/20 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 transition-all active:scale-95">
                Save Changes
            </button>
        </div>
    </form>
    `;
}
