// /resources/js/utils/schedules/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { loadPartial } from '../spa-router.js';
import { refreshMasterSchedule } from './view-all-handler.js';

function getPayload(form, mode, seasonIdEnc) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    data.mode = mode;
    data.txt_season_id = seasonIdEnc;

    if (mode === 'edit') {
        const scheduleId = form.dataset.encodedId || form.getAttribute('data-encoded-id');

        if (scheduleId) {
            data.txt_schedule_id = scheduleId;
        } else {
            console.error('Edit mode detected but no Schedule ID found on form element.');
        }
    }

    return data;
}

/**
 * Scrolls the saved game's row into the middle of the viewport and briefly
 * highlights it. Needed because the table always re-sorts by date/time on
 * reload (see SchedulesController::getScheduleDetail()), so a game dated
 * further out than what's currently on screen can land anywhere in a
 * season's full list -- with no scroll cue, a save that actually succeeded
 * just looks like nothing happened.
 */
function scrollToAndHighlightGame(encodedGameId) {
    if (!encodedGameId) return;

    const row = document.querySelector(`tr[data-game-id="${encodedGameId}"]`);
    if (!row) return;

    row.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const flashClasses = ['bg-secondary-100', 'dark:bg-secondary-900/40', 'ring-2', 'ring-inset', 'ring-secondary-400'];
    row.classList.add(...flashClasses, 'transition-colors', 'duration-700');
    setTimeout(() => row.classList.remove(...flashClasses), 2200);
}

export function handleScheduleFormSubmission(
    form,
    mode,
    modalInstance,
    seasonIdEnc
) {
    if (form._scheduleFormListenerAttached) return;
    form._scheduleFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    let apiMsg = form.querySelector('.api-message');

    if (!apiMsg) {
        apiMsg = document.createElement('div');
        apiMsg.className = 'api-message mt-4 transition-all duration-300';
        form.appendChild(apiMsg);
    }

    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!seasonIdEnc) {
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Error: Season context missing. Refresh page.</div>`;
            return;
        }

        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form, mode, seasonIdEnc);
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';

            const response = await fetch(`${baseUrl}api/schedules`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Schedule saved successfully.'}
                    </div>
                `;

                submitBtn.style.display = 'none';
                const savedGameId = result.gameId;

                setTimeout(async () => {
                    if (modalInstance && typeof modalInstance.close === 'function') {
                        modalInstance.close();
                    }

                    const viewAllBtn = document.querySelector('#view-all-schedules-btn');
                    const isViewAllMode = viewAllBtn && viewAllBtn.classList.contains('hidden');

                    if (isViewAllMode) {
                        await refreshMasterSchedule();
                    } else {
                        await loadPartial(window.location.href);
                    }

                    scrollToAndHighlightGame(savedGameId);
                }, 600);

            } else {
                apiMsg.innerHTML = (result.messages || ['Check your input']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${msg}
                    </div>
                `).join('');
            }

        } catch (err) {
            console.error('Schedule Submission Error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Unexpected error. Please try again.</div>`;
        } finally {
            if (submitBtn.style.display !== 'none') {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
