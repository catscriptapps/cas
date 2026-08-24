// /resources/js/utils/contacts/view-contact.js

export function initViewContact() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-contact-trigger');
        if (!trigger) return;

        const data = trigger.dataset;
        const modal = document.getElementById('view-contact-modal');
        if (!modal) return;

        const nameEl = document.getElementById('view-contact-name');
        const avatarEl = document.getElementById('view-contact-avatar');
        const orgEl = document.getElementById('view-contact-org');

        const contactName = data.name || 'Unknown Contact';

        if (nameEl) nameEl.textContent = contactName;
        if (orgEl) orgEl.textContent = data.org || 'Independent Official';
        if (avatarEl) avatarEl.textContent = contactName.charAt(0).toUpperCase();

        const emailEl = document.getElementById('view-contact-email');
        const phoneEl = document.getElementById('view-contact-phone');
        const leaguesEl = document.getElementById('view-contact-leagues');
        const statusEl = document.getElementById('view-contact-status');
        const emergencyBadge = document.getElementById('view-contact-emergency-badge');

        if (emailEl) emailEl.textContent = data.email || '—';
        if (phoneEl) phoneEl.textContent = data.phone || '—';
        if (leaguesEl) leaguesEl.textContent = data.leagues || 'All Leagues';

        if (statusEl) {
            statusEl.textContent = data.role || 'Official';
            statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30 font-sans';
        }

        if (emergencyBadge) {
            const isEmergency = data.emergency === "Yes";
            emergencyBadge.classList.toggle('hidden', !isEmergency);
        }

        const editBtn = document.getElementById('view-contact-edit-btn');
        if (editBtn) {
            editBtn.onclick = () => {
                modal.classList.add('hidden');
                const row = trigger.closest('tr');
                if (row) {
                    const rowEditBtn = row.querySelector('.edit-contact-btn');
                    if (rowEditBtn) rowEditBtn.click();
                }
            };
        }

        modal.classList.remove('hidden');
    });

    document.addEventListener('click', (e) => {
        const isCloseTrigger = e.target.closest('.close-view-modal') || e.target.id === 'close-view-modal-overlay';
        if (isCloseTrigger) {
            const modal = document.getElementById('view-contact-modal');
            if (modal) modal.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('view-contact-modal');
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        }
    });
}
