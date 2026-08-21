// /resources/js/utils/companies/company-admins.js

import { showToast } from '../../ui/toast.js';
import { createDeleteHandler } from '../../factories/delete-factory.js';
import { buttonSpinner } from '../spinner-utils.js';

let currentCompanyEncodedId = null;
let listenersAttached = false;

const EMPTY_STATE = `<p class="text-xs text-gray-400 italic text-center py-3">No company admins yet.</p>`;

function renderAdminRow(admin) {
    const statusBadge = admin.is_active
        ? '<span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">Active</span>'
        : '<span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-gray-100 dark:bg-gray-800 text-gray-500">Inactive</span>';

    return `
        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800" data-admin-encoded-id="${admin.encoded_id}">
            <div class="min-w-0">
                <p class="text-sm font-bold text-navy-900 dark:text-white truncate">${admin.full_name}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">${admin.email}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                ${statusBadge}
                <button type="button" class="delete-company-admin-btn text-gray-400 hover:text-red-600 p-1" data-encoded-id="${admin.encoded_id}" title="Remove admin">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    `;
}

/**
 * Fetches and renders the Company Admins list for the company currently
 * open in the view modal. Also resets any in-progress "Add Admin" form
 * left over from a previously viewed company.
 */
export async function loadCompanyAdmins(encodedCompanyId) {
    currentCompanyEncodedId = encodedCompanyId;

    const listEl = document.getElementById('view-company-admins-list');
    const form = document.getElementById('view-company-add-admin-form');
    form?.classList.add('hidden');
    form?.reset();

    if (!listEl) return;

    listEl.innerHTML = `<div class="flex justify-center py-4"><span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-primary-500"></span></div>`;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const res = await fetch(`${baseUrl}api/company-admins?company_id=${encodeURIComponent(encodedCompanyId)}`);
        const result = await res.json();

        if (!result.success || !result.data || result.data.length === 0) {
            listEl.innerHTML = EMPTY_STATE;
            return;
        }

        listEl.innerHTML = result.data.map(renderAdminRow).join('');
    } catch (err) {
        console.error('Failed to load company admins:', err);
        listEl.innerHTML = `<p class="text-xs text-red-500 text-center py-3">Failed to load admins.</p>`;
    }
}

/**
 * Wires up the "Add Admin" toggle/form and delete delegation once globally.
 */
export function initCompanyAdminsPanel() {
    if (listenersAttached) return;
    listenersAttached = true;

    document.addEventListener('click', (e) => {
        if (e.target.closest('#view-company-add-admin-toggle')) {
            document.getElementById('view-company-add-admin-form')?.classList.toggle('hidden');
            return;
        }

        if (e.target.closest('#view-company-add-admin-cancel')) {
            const form = document.getElementById('view-company-add-admin-form');
            form?.classList.add('hidden');
            form?.reset();
            return;
        }

        const deleteBtn = e.target.closest('.delete-company-admin-btn');
        if (deleteBtn) {
            const encodedId = deleteBtn.dataset.encodedId;
            const row = deleteBtn.closest('[data-admin-encoded-id]');
            if (!encodedId || !row) return;

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const deleteHandler = createDeleteHandler(`${baseUrl}api/company-admins`, 'Company Admin');

            deleteHandler.showConfirmation(encodedId, row, (result) => {
                const success = result === true || result?.success;
                if (!success) return;

                showToast('Company admin removed', 'success');
                row.remove();

                const listEl = document.getElementById('view-company-admins-list');
                if (listEl && listEl.children.length === 0) {
                    listEl.innerHTML = EMPTY_STATE;
                }
            });
        }
    });

    document.getElementById('view-company-add-admin-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentCompanyEncodedId) return;

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalLabel = submitBtn.innerHTML;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (data.password !== data.passwordConfirmation) {
            showToast('Passwords do not match', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;

        try {
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const res = await fetch(`${baseUrl}api/company-admins`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    company_id: currentCompanyEncodedId,
                    first_name: data.firstName,
                    last_name: data.lastName,
                    email: data.email,
                    password: data.password,
                    password_confirmation: data.passwordConfirmation,
                }),
            });
            const result = await res.json();

            if (result.success) {
                showToast(result.messages?.[0] || 'Company admin added', 'success');
                form.reset();
                form.classList.add('hidden');
                loadCompanyAdmins(currentCompanyEncodedId);
            } else {
                showToast(result.messages?.[0] || 'Failed to add admin', 'error');
            }
        } catch (err) {
            console.error('Add company admin error:', err);
            showToast('Server connection error', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;
        }
    });
}
