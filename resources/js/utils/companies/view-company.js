// /resources/js/utils/companies/view-company.js

import { loadCompanyAdmins } from './company-admins.js';
import { initLogoForCompany } from './company-logo.js';

export function initViewCompany() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-company-trigger');
        if (!trigger) return;

        const data = trigger.dataset;
        const modal = document.getElementById('view-company-modal');
        if (!modal) return;

        // 1. Header
        const nameEl = document.getElementById('view-company-name');
        const emailSubEl = document.getElementById('view-company-email-sub');

        if (nameEl) nameEl.textContent = data.companyName;
        if (emailSubEl) emailSubEl.textContent = data.email;

        // 1b. Logo (header avatar + the "Company Logo" management section)
        initLogoForCompany(data.encodedId, data.companyName, data.logoUrl);

        // 2. Slogan
        const sloganEl = document.getElementById('view-company-slogan');
        if (sloganEl) {
            if (data.slogan && data.slogan.trim() !== '') {
                sloganEl.textContent = `“${data.slogan}”`;
                sloganEl.classList.remove('hidden');
            } else {
                sloganEl.classList.add('hidden');
            }
        }

        // 3. Location
        const combinedLocEl = document.getElementById('view-company-combined-location');
        if (combinedLocEl) {
            const city = data.city || 'N/A';
            const region = data.regionName || '';
            const country = data.countryName || '';
            combinedLocEl.textContent = `${city}, ${region} (${country})`;
        }

        // 4. Phone
        const phoneEl = document.getElementById('view-company-phone');
        if (phoneEl) phoneEl.textContent = data.phone || 'N/A';

        // 5. Website & Toll-Free
        const websiteEl = document.getElementById('view-company-website');
        if (websiteEl) websiteEl.textContent = data.website && data.website.trim() !== '' ? data.website : 'No website on file';

        const tollFreeEl = document.getElementById('view-company-toll-free');
        if (tollFreeEl) {
            if (data.tollFree && data.tollFree.trim() !== '') {
                tollFreeEl.textContent = data.tollFree;
                tollFreeEl.classList.remove('hidden');
            } else {
                tollFreeEl.classList.add('hidden');
            }
        }

        // 6. Status & Joined Date
        const statusEl = document.getElementById('view-company-status');
        const joinedEl = document.getElementById('view-company-joined');

        if (joinedEl) joinedEl.textContent = `Joined ${data.joined}`;

        if (statusEl) {
            const isActive = data.isActive === '1';
            statusEl.textContent = isActive ? 'Current' : 'Archived';
            statusEl.className = isActive
                ? 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 border border-orange-100 dark:border-orange-800/30'
                : 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700';
        }

        // 7. Edit Button logic
        const editBtn = document.getElementById('view-company-edit-btn');
        if (editBtn) {
            editBtn.onclick = () => {
                modal.classList.add('hidden');
                const row = trigger.closest('tr');
                if (row) {
                    const rowEditBtn = row.querySelector('.edit-company-btn');
                    if (rowEditBtn) rowEditBtn.click();
                }
            };
        }

        // 8. Company Admins panel
        if (data.encodedId) loadCompanyAdmins(data.encodedId);

        modal.classList.remove('hidden');
    });

    // Close Modals (Overlay/Close Button)
    document.addEventListener('click', (e) => {
        const isCloseTrigger = e.target.closest('.close-view-company-modal') || e.target.id === 'close-view-company-modal-overlay';
        if (isCloseTrigger) {
            const modal = document.getElementById('view-company-modal');
            if (modal) modal.classList.add('hidden');
        }
    });

    // Close Modal (Escape)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('view-company-modal');
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        }
    });
}
