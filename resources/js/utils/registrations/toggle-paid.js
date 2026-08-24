// /resources/js/utils/registrations/toggle-paid.js

import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Lets an admin flip a registration's Paid/Unpaid status directly from the
 * table row (desktop Payment column or the mobile-collapsed row), without
 * opening the full edit modal. Reuses the same save() endpoint the edit
 * modal posts to -- sending only `has_paid` leaves every other field
 * untouched server-side (see RegistrationsController::save()).
 */
export function initTogglePaidStatus(tableSelector = '#registrations-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    tbody.addEventListener('click', async (e) => {
        const btn = e.target.closest('.toggle-paid-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const encodedId = btn.dataset.encodedId;
        const nextHasPaid = btn.dataset.hasPaid === '1' ? 0 : 1;
        if (!encodedId) return;

        // Every toggle-paid-btn for this row (desktop + mobile copies) gets
        // disabled together so a double-click can't fire two requests.
        const row = btn.closest('tr[data-encoded-id]');
        const allButtonsInRow = row ? row.querySelectorAll('.toggle-paid-btn') : [btn];
        allButtonsInRow.forEach((b) => {
            b.disabled = true;
            b.classList.add('opacity-50', 'cursor-wait');
        });

        try {
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/registrations`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ encoded_id: encodedId, has_paid: nextHasPaid }),
            });
            const result = await response.json();

            if (!result.success) {
                showToast(result.messages?.[0] || 'Could not update payment status.', 'error');
                allButtonsInRow.forEach((b) => {
                    b.disabled = false;
                    b.classList.remove('opacity-50', 'cursor-wait');
                });
                return;
            }

            if (row && result.rowHtml) {
                row.outerHTML = result.rowHtml;
            }

            updateCount('registration', tableSelector, '#registrations-count');
            showToast(nextHasPaid ? 'Marked as paid' : 'Marked as unpaid', 'success');
        } catch (err) {
            console.error('Toggle paid status error:', err);
            showToast('A network error occurred.', 'error');
            allButtonsInRow.forEach((b) => {
                b.disabled = false;
                b.classList.remove('opacity-50', 'cursor-wait');
            });
        }
    });
}
