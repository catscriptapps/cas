// /resources/js/forms/season-form.js

export function seasonForm({
    mode = 'add',
    divisionId = '',
    seasonYear = new Date().getFullYear(),
    buttonLabel = 'Create Season',
    formId = 'season-add-form',
    divisionList = []
}) {
    const idPrefix = 'season-setup';
    const inputClasses = `block w-full rounded-xl border border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-primary-400 focus:ring-primary-400 sm:text-sm transition-all duration-200 py-2.5 px-4`;
    const labelClasses = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5 ml-1";

    const grouped = divisionList.reduce((acc, div) => {
        const sport = div.sport_name || 'OTHER';
        const league = div.league_name || 'General';
        if (!acc[sport]) acc[sport] = {};
        if (!acc[sport][league]) acc[sport][league] = [];
        acc[sport][league].push(div);
        return acc;
    }, {});

    let optionsHtml = `<option value="">Select a Division</option>`;

    for (const [sport, leagues] of Object.entries(grouped)) {
        optionsHtml += `
            <optgroup label="">
                <option disabled class="text-primary-600 font-bold uppercase">${sport}</option>
            </optgroup>`;

        for (const [league, divisions] of Object.entries(leagues)) {
            optionsHtml += `<optgroup label="&nbsp;&nbsp;${league}" class="text-secondary-600 italic">`;

            divisions.forEach(d => {
                const isSelected = String(d.division_id) === String(divisionId) ? 'selected' : '';
                optionsHtml += `
                    <option value="${d.division_id}" ${isSelected} class="text-gray-900 dark:text-white">
                        &nbsp;&nbsp;&nbsp;&bull; ${d.division}
                    </option>`;
            });
            optionsHtml += `</optgroup>`;
        }
    }

    return `
    <form id="${formId}" class="w-full max-w-2xl mx-auto space-y-6 p-1 font-sans" novalidate>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
            <div class="md:col-span-12">
                <label for="${idPrefix}-division" class="${labelClasses}">Division</label>
                <select id="${idPrefix}-division" name="division_id" required class="${inputClasses}">
                    ${optionsHtml}
                </select>
            </div>

            <div class="md:col-span-4">
                <label for="${idPrefix}-year" class="${labelClasses}">Season Year</label>
                <input type="number" required id="${idPrefix}-year" name="season_year"
                    placeholder="2026" value="${seasonYear}" class="${inputClasses}" />
            </div>
        </div>

        <div class="p-4 bg-primary-50 dark:bg-primary-950/20 rounded-2xl border border-primary-100 dark:border-primary-900/50">
            <p class="text-xs text-primary-700 dark:text-primary-300 leading-relaxed">
                <strong>Note:</strong> Creating a season entry links a division to a specific calendar year.
            </p>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-400 px-10 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-400/30 hover:bg-primary-500 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}
