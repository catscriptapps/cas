// /resources/js/utils/registrations/view-registration.js

export function initViewRegistration() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-registration-trigger');
        if (!trigger) return;

        const data = trigger.dataset;
        const modal = document.getElementById('view-registration-modal');
        if (!modal) return;

        const set = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        };

        set('view-reg-name', data.fullName || 'Unknown');
        set('view-reg-email', data.email || '');
        set('view-reg-division', data.divisionName || 'N/A');
        set('view-reg-amount', `$${parseFloat(data.amountPaid || 0).toFixed(2)}`);
        set('view-reg-phone', data.phone || 'N/A');
        set('view-reg-location', [data.city, data.regionName].filter(Boolean).join(', ') || 'N/A');
        set('view-reg-position', data.desiredPosition || 'N/A');
        set('view-reg-source', data.sourceLabel || 'N/A');
        set('view-reg-joined', `Registered ${data.joined || ''}`);

        const fallback = document.getElementById('view-reg-avatar-fallback');
        if (fallback) fallback.textContent = data.fullName ? data.fullName.charAt(0).toUpperCase() : '?';

        const notesWrapper = document.getElementById('view-reg-notes-wrapper');
        const notesEl = document.getElementById('view-reg-notes');
        if (notesEl && notesWrapper) {
            if (data.specialRequests && data.specialRequests.trim() !== '') {
                notesEl.textContent = data.specialRequests;
                notesWrapper.classList.remove('hidden');
            } else {
                notesWrapper.classList.add('hidden');
            }
        }

        const statusEl = document.getElementById('view-reg-status');
        if (statusEl) {
            const isActive = data.isActive === '1';
            statusEl.textContent = isActive ? 'Current' : 'Archived';
            statusEl.className = isActive
                ? 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30'
                : 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700';
        }

        const paidEl = document.getElementById('view-reg-paid-status');
        if (paidEl) {
            const hasPaid = data.hasPaid === '1';
            paidEl.textContent = hasPaid ? 'Paid' : 'Unpaid';
            paidEl.className = hasPaid
                ? 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-secondary-50 dark:bg-secondary-900/20 text-secondary-700 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-800/30'
                : 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800/30';
        }

        const editBtn = document.getElementById('view-registration-edit-btn');
        if (editBtn) {
            editBtn.onclick = () => {
                modal.classList.add('hidden');
                const row = trigger.closest('tr');
                const rowEditBtn = row?.querySelector('.edit-registration-btn');
                if (rowEditBtn) rowEditBtn.click();
            };
        }

        modal.classList.remove('hidden');
    });

    document.addEventListener('click', (e) => {
        const isCloseTrigger = e.target.closest('.close-view-registration') || e.target.id === 'close-view-registration-overlay';
        if (isCloseTrigger) {
            document.getElementById('view-registration-modal')?.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('view-registration-modal');
            if (modal && !modal.classList.contains('hidden')) modal.classList.add('hidden');
        }
    });
}
