// /resources/js/utils/seasons/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { updateCount } from '../../components/table-pagination-count.js';

function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const internalPath = window.location.pathname.startsWith(baseUrl)
        ? window.location.pathname.slice(baseUrl.length)
        : window.location.pathname;

    const firstSegment = internalPath.split('/').filter(Boolean)[0];
    const pageContext = ['stats', 'gamesheets'].includes(firstSegment) ? firstSegment : 'schedules';

    return {
        division_id: data.division_id,
        season_year: data.season_year,
        page_context: pageContext,
    };
}

export function handleSeasonFormSubmission(
    form,
    mode,
    modalInstance,
    tableSelector = '#seasons-tbody'
) {
    if (form._seasonFormListenerAttached) return;
    form._seasonFormListenerAttached = true;

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

        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';

            const response = await fetch(`${baseUrl}api/seasons`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                const tbody = document.querySelector(tableSelector);

                if (tbody) {
                    const emptyStateRow = tbody.querySelector('.empty-state-row') ||
                        tbody.querySelector('td[colspan]')?.closest('tr');
                    if (emptyStateRow) emptyStateRow.remove();

                    if (result.rowHtml) {
                        tbody.insertAdjacentHTML('afterbegin', result.rowHtml);
                    }

                    updateCount('season', tableSelector, '#seasons-count');
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Season created successfully.'}
                    </div>
                `;

                submitBtn.style.display = 'none';

                setTimeout(() => {
                    if (modalInstance && typeof modalInstance.close === 'function') {
                        modalInstance.close();
                    }
                }, 800);
            } else {
                apiMsg.innerHTML = (result.messages || ['Check your input']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${msg}
                    </div>
                `).join('');
            }
        } catch (err) {
            console.error('Season Submission Error:', err);
            apiMsg.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                    Unexpected error. Please try again.
                </div>`;
        } finally {
            if (submitBtn.style.display !== 'none') {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
