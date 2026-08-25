// /resources/js/forms/incident-report-form.js

/**
 * Strips legacy database artifacts (escaped quotes, literal \r\n, <br> tags)
 * from free-text fields pulled off a row's data-* attributes before they're
 * dropped back into a form input/textarea.
 */
const cleanLegacy = (str) => {
    if (!str || typeof str !== 'string') return '';
    return str
        .replace(/\\"/g, '"')
        .replace(/\\'/g, "'")
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/\\r\\n|\\n|\\r/g, '\n')
        .trim();
};

export function incidentReportForm({
    mode = 'add',
    incidentDate = '',
    incidentTime = '',
    location = '',
    teamsInvolved = '',
    personsInvolved = '',
    refInvolved = '',
    timekeeper = '',
    incident = '',
    equipmentWorn = '',
    medicalAssistance = '',
    managerName = '',
    managerTime = '',
    refereeOutcome = '',
    signature = '',
    isActive = true,
    buttonLabel = 'Save',
    formId = 'incident-report-form',
    encodedId = null,
}) {
    const cleanLoc = cleanLegacy(location);
    const cleanTeams = cleanLegacy(teamsInvolved);
    const cleanPersons = cleanLegacy(personsInvolved);
    const cleanRef = cleanLegacy(refInvolved);
    const cleanTimekeeper = cleanLegacy(timekeeper);
    const cleanEquip = cleanLegacy(equipmentWorn);
    const cleanIncident = cleanLegacy(incident);
    const cleanMedical = cleanLegacy(medicalAssistance);
    const cleanManager = cleanLegacy(managerName);
    const cleanOutcome = cleanLegacy(refereeOutcome);
    const cleanSignature = cleanLegacy(signature);

    const idPrefix = mode === 'edit' ? 'incident-edit' : 'incident';
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

    const textareaClasses = `${inputClasses} min-h-[120px] resize-y`;
    const labelClasses = 'block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5 ml-1';

    return `
    <form
        id="${formId}"
        class="w-full max-w-5xl mx-auto space-y-6 p-1 font-sans"
        novalidate
        ${dataEncodedIdAttr}>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
                <label for="${idPrefix}-date" class="${labelClasses}">Incident Date</label>
                <input type="date" required id="${idPrefix}-date" name="incident_date"
                    value="${incidentDate}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-3">
                <label for="${idPrefix}-time" class="${labelClasses}">Incident Time</label>
                <input type="time" id="${idPrefix}-time" name="incident_time"
                    value="${incidentTime}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-6">
                <label for="${idPrefix}-location" class="${labelClasses}">Location / Rink</label>
                <input type="text" required id="${idPrefix}-location" name="location"
                    placeholder="e.g. Rink 1, West Arena" value="${cleanLoc}" class="${inputClasses}" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6">
                <label for="${idPrefix}-teams" class="${labelClasses}">Teams Involved</label>
                <input type="text" id="${idPrefix}-teams" name="teams_involved"
                    placeholder="Team A vs Team B" value="${cleanTeams}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-6">
                <label for="${idPrefix}-persons" class="${labelClasses}">Persons Involved</label>
                <input type="text" id="${idPrefix}-persons" name="persons_involved"
                    placeholder="Names, jersey numbers..." value="${cleanPersons}" class="${inputClasses}" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 border-t border-gray-100 dark:border-gray-800 pt-4">
            <div class="md:col-span-4">
                <label for="${idPrefix}-ref" class="${labelClasses}">Referee(s)</label>
                <input type="text" id="${idPrefix}-ref" name="ref_involved"
                    placeholder="Full name(s)" value="${cleanRef}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-timekeeper" class="${labelClasses}">Timekeeper</label>
                <input type="text" id="${idPrefix}-timekeeper" name="timekeeper"
                    placeholder="Full name" value="${cleanTimekeeper}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-equipment" class="${labelClasses}">Equipment Worn</label>
                <input type="text" id="${idPrefix}-equipment" name="equipment_worn"
                    placeholder="e.g. Full gear, no helmet" value="${cleanEquip}" class="${inputClasses}" />
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label for="${idPrefix}-incident-desc" class="${labelClasses}">Description of Incident</label>
                <textarea id="${idPrefix}-incident-desc" name="incident"
                    placeholder="Describe exactly what happened..." class="${textareaClasses}">${cleanIncident}</textarea>
            </div>
            <div>
                <label for="${idPrefix}-medical" class="${labelClasses}">Medical Assistance Rendered</label>
                <textarea id="${idPrefix}-medical" name="medical_assistance"
                    placeholder="First aid provided, EMS called, etc..." class="${textareaClasses}">${cleanMedical}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 border-t border-gray-100 dark:border-gray-800 pt-4">
            <div class="md:col-span-4">
                <label for="${idPrefix}-manager-name" class="${labelClasses}">Manager on Duty</label>
                <input type="text" id="${idPrefix}-manager-name" name="manager_name"
                    value="${cleanManager}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-manager-time" class="${labelClasses}">Time Notified</label>
                <input type="time" id="${idPrefix}-manager-time" name="manager_time"
                    value="${managerTime}" class="${inputClasses}" />
            </div>
            <div class="md:col-span-4">
                <label for="${idPrefix}-outcome" class="${labelClasses}">Referee Outcome</label>
                <input type="text" id="${idPrefix}-outcome" name="referee_outcome"
                    placeholder="e.g. Game Misconduct" value="${cleanOutcome}" class="${inputClasses}" />
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-end justify-between pt-6 border-t border-gray-100 dark:border-gray-800 gap-6">

            <div class="flex-1 max-w-md">
                <label for="${idPrefix}-signature" class="block text-sm font-bold text-primary-600 dark:text-primary-400 mb-1.5 ml-1 uppercase tracking-tight">
                    Electronic Signature (Required)
                </label>
                <input type="text" required id="${idPrefix}-signature" name="name_e_signature"
                    placeholder="Type your full name to sign"
                    value="${cleanSignature}"
                    class="${inputClasses} border-primary-300 dark:border-primary-800 focus:ring-primary-600 italic font-medium" />
            </div>

            <div class="flex items-center space-x-8">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="status_id" value="1" class="sr-only peer" ${isActive ? 'checked' : ''}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-500"></div>
                    <span class="ml-3 text-sm font-bold text-gray-700 dark:text-gray-300">Mark as Filed</span>
                </label>

                <button type="submit" id="${idPrefix}-submit"
                    class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-12 py-3 text-sm font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all active:scale-95">
                    ${buttonLabel}
                </button>
            </div>
        </div>
    </form>
    `;
}
