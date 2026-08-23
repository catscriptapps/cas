// /resources/js/forms/sport-form.js

export function sportForm({ formId = 'sport-form', sportName = '', isActive = true, encodedId = null }) {
    const inputClasses = `
        block w-full rounded-xl border border-gray-400 dark:border-gray-600
        bg-white dark:bg-gray-900 text-gray-900 dark:text-white
        placeholder:text-gray-400 focus:border-primary-400 focus:ring-primary-400
        sm:text-sm transition-all duration-200 py-2.5 px-4
    `.replace(/\s+/g, ' ').trim();

    return `
    <form id="${formId}" class="w-full max-w-md mx-auto space-y-6 p-1 font-sans" novalidate data-encoded-id="${encodedId ?? ''}">
        <div>
            <label for="sport-name" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5 ml-1">Sport Name</label>
            <input type="text" required id="sport-name" name="sport_name" value="${sportName}"
                oninput="this.value = this.value.toUpperCase()"
                class="${inputClasses}" />
        </div>

        <label class="relative inline-flex items-center cursor-pointer group">
            <input type="checkbox" name="isActive" class="sr-only peer" ${isActive ? 'checked' : ''}>
            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-primary-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
            <span class="ml-3 text-sm font-bold text-gray-700 dark:text-gray-300">Active</span>
        </label>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary-500 px-10 py-3 text-sm font-bold text-white shadow-lg shadow-primary-500/20 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 transition-all active:scale-95">
                Save
            </button>
        </div>
    </form>
    `;
}
