// /resources/js/utils/schedules/delete-schedule.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { loadPartial } from '../spa-router.js';

export function initDeleteSchedule(containerSelector = '.schedules-container') {
    const container = document.querySelector(containerSelector) || document.querySelector('main');
    if (!container) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/schedules`, 'Game');

    container.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-schedule-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const row = btn.closest('tr[data-game-id]');
        const encodedId = row?.dataset.gameId;

        if (!encodedId || !row) {
            console.error('Delete failed: Missing encoded game ID or row element.');
            return;
        }

        deleteHandler.showConfirmation(encodedId, row, (result) => {
            if (result?.success === false) {
                showToast(result.messages?.[0] || 'Could not delete game.', 'error');
                return;
            }

            showToast('Game successfully removed', 'success');
            setTimeout(() => {
                loadPartial(window.location.href);
            }, 400);
        });
    });
}
