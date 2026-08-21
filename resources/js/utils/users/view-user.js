// /resources/js/utils/users/view-user.js

export function initViewUser() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.view-user-trigger');
        if (!trigger) return;

        const data = trigger.dataset;
        const modal = document.getElementById('view-user-modal');
        if (!modal) return;

        // 1. Populate Header & Avatar
        const nameEl = document.getElementById('view-user-name');
        const emailSubEl = document.getElementById('view-user-email-sub');
        const avatarContainer = document.getElementById('view-user-avatar-container');
        const fallbackEl = document.getElementById('view-user-avatar-fallback');
        
        if (nameEl) nameEl.textContent = data.fullName;
        if (emailSubEl) emailSubEl.textContent = data.email;

        if (avatarContainer && fallbackEl) {
            const existingImg = avatarContainer.querySelector('img');
            if (existingImg) existingImg.remove();

            if (data.avatarUrl && data.avatarUrl.trim() !== '') {
                const img = document.createElement('img');
                img.src = data.avatarUrl;
                img.alt = data.fullName;
                img.className = 'h-full w-full rounded-full object-cover border-2 border-white dark:border-gray-800 shadow-sm';
                img.onerror = () => { 
                    img.classList.add('hidden'); 
                    fallbackEl.classList.remove('hidden'); 
                };
                fallbackEl.classList.add('hidden');
                avatarContainer.appendChild(img);
            } else {
                fallbackEl.classList.remove('hidden');
                fallbackEl.textContent = data.fullName ? data.fullName.charAt(0).toUpperCase() : '?';
            }
        }

        // 2. Location Logic
        const combinedLocEl = document.getElementById('view-user-combined-location');
        if (combinedLocEl) {
            const city = data.city || 'N/A';
            const region = data.regionName || '';
            const country = data.countryName || '';
            combinedLocEl.textContent = `${city}, ${region} (${country})`;
        }

// 3. User Role (Account Classification)
        const rolesContainer = document.getElementById('view-user-roles-container');
        if (rolesContainer) {
            const role = data.userType || '';
            // Using Slate as the "Navy" hue to ensure the color renders
            // Light mode: bg-slate-100 (light grey-blue) / Dark mode: bg-slate-800
            rolesContainer.innerHTML = role
                ? `<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition-colors">${role}</span>`
                : '<span class="text-gray-400 text-xs italic">No role assigned</span>';
        }

        // 4. Status & Joined Date
        const statusEl = document.getElementById('view-user-status');
        const joinedEl = document.getElementById('view-user-joined');
        
        if (joinedEl) joinedEl.textContent = `Joined ${data.joined}`;

        if (statusEl) {
            const isActive = data.isActive === '1';
            statusEl.textContent = isActive ? 'Current' : 'Archived';
            statusEl.className = isActive 
                ? 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 border border-orange-100 dark:border-orange-800/30'
                : 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700';
        }

        // 5. Edit Button logic
        const editBtn = document.getElementById('view-user-edit-btn');
        if (editBtn) {
            editBtn.onclick = () => {
                modal.classList.add('hidden');
                const row = trigger.closest('tr');
                if (row) {
                    const rowEditBtn = row.querySelector('.edit-user-btn');
                    if (rowEditBtn) rowEditBtn.click();
                }
            };
        }

        modal.classList.remove('hidden');
    });

    // Close Modals (Overlay/Close Button)
    document.addEventListener('click', (e) => {
        const isCloseTrigger = e.target.closest('.close-view-modal') || e.target.id === 'close-view-modal-overlay';
        if (isCloseTrigger) {
            const modal = document.getElementById('view-user-modal');
            if (modal) modal.classList.add('hidden');
        }
    });

    // Close Modal (Escape)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('view-user-modal');
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        }
    });
}