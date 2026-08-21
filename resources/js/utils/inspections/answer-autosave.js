// /resources/js/utils/inspections/answer-autosave.js

const debounceTimers = new Map();

function getInspectionId() {
    return document.querySelector('[data-inspection-id]')?.dataset.inspectionId;
}

function collectAnswerPayload(card) {
    const taskSelect = card.querySelector('[data-role="tasks-select"]');
    const notesTextarea = card.querySelector('[data-role="notes-textarea"]');
    const optionCheckboxes = card.querySelectorAll('[data-role="option-checkbox"]:checked');
    const fieldInputs = card.querySelectorAll('[data-role="field-input"]');

    const fields = {};
    fieldInputs.forEach((input) => {
        fields[input.dataset.fieldId] = input.value;
    });

    return {
        question_id: card.dataset.questionId,
        tasks: taskSelect ? taskSelect.value : 0,
        notes: notesTextarea ? notesTextarea.value : '',
        option_ids: Array.from(optionCheckboxes).map((cb) => cb.value),
        fields,
    };
}

async function saveAnswer(card) {
    const inspectionId = getInspectionId();
    if (!inspectionId) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const payload = { inspection_id: inspectionId, ...collectAnswerPayload(card) };

    try {
        const response = await fetch(`${baseUrl}api/inspection-answer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();

        const indicator = card.querySelector('[data-role="save-indicator"]');
        if (result.success && indicator) {
            indicator.classList.remove('hidden');
            clearTimeout(indicator._hideTimer);
            indicator._hideTimer = setTimeout(() => indicator.classList.add('hidden'), 1500);
        }
    } catch (err) {
        console.error('Failed to save answer:', err);
    }
}

/**
 * Swaps the Status select's background/border/text color to match whichever
 * option is now picked, instantly on change -- each <option> carries its
 * own color classes (data-classes, from question-answer-row.php's
 * $taskSelectClassMap) so PHP stays the single source of truth for the
 * palette instead of duplicating it here.
 */
function updateTaskSelectColor(select) {
    const baseClasses = (select.dataset.baseClasses || '').split(' ').filter(Boolean);
    const selectedOption = select.options[select.selectedIndex];
    const colorClasses = (selectedOption?.dataset.classes || '').split(' ').filter(Boolean);
    select.className = [...baseClasses, ...colorClasses].join(' ');
}

function scheduleSave(card) {
    const key = card.dataset.questionId;
    clearTimeout(debounceTimers.get(key));
    debounceTimers.set(key, setTimeout(() => saveAnswer(card), 600));
}

/**
 * Delegated to #inspection-section-content (which persists across tab
 * switches -- only its innerHTML is replaced), so this only needs wiring
 * once per page load, not re-wiring after every section-content fetch.
 */
export function initAnswerAutosave() {
    const container = document.getElementById('inspection-section-content');
    if (!container || container._answerAutosaveAttached) return;
    container._answerAutosaveAttached = true;

    container.addEventListener('change', (e) => {
        if (!e.target.matches('[data-role="tasks-select"], [data-role="option-checkbox"]')) return;
        if (e.target.matches('[data-role="tasks-select"]')) updateTaskSelectColor(e.target);
        const card = e.target.closest('[data-question-id]');
        if (card) scheduleSave(card);
    });

    container.addEventListener('input', (e) => {
        if (!e.target.matches('[data-role="notes-textarea"], [data-role="field-input"]')) return;
        const card = e.target.closest('[data-question-id]');
        if (card) scheduleSave(card);
    });
}
