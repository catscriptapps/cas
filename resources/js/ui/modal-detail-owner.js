// /resources/js/ui/modal-detail-owner.js

export function modalDetailOwner(modalDetailOwnerId, data) {
    const ownerNameEl = document.getElementById('view-'+modalDetailOwnerId+'-owner-name');
    const ownerLocEl = document.getElementById('view-'+modalDetailOwnerId+'-owner-location');
    const ownerAvatarContainer = document.getElementById('view-'+modalDetailOwnerId+'-owner-avatar-container');
    const userTypesWrapper = document.getElementById('view-'+modalDetailOwnerId+'-user-types-wrapper');

    if (ownerNameEl) ownerNameEl.textContent = data.ownerName || 'Unknown User';
    if (ownerLocEl) {
        ownerLocEl.textContent = `${data.ownerRegion || 'Unknown Region'}, ${data.ownerCountry || 'Unknown Country'}`;
    }

    // Avatar / Initial logic specifically for the Body Profile Section
    if (ownerAvatarContainer) {
        if (data.ownerAvatar && data.ownerAvatar.trim() !== '') {
            ownerAvatarContainer.innerHTML = `<img src="${data.ownerAvatar}" class="w-full h-full object-cover" alt="${data.ownerName}">`;
        } else {
            ownerAvatarContainer.innerHTML = `<span class="text-2xl font-black text-primary-400">${data.ownerInitial || 'U'}</span>`;
        }
    }

    // User Type Badges (Exact mirror of card logic)
    if (userTypesWrapper) {
        userTypesWrapper.innerHTML = '';
        try {
            const types = JSON.parse(data.userTypes || '["Client"]');
            types.forEach(type => {
                const span = document.createElement('span');
                span.className = "text-[9px] font-black px-1.5 py-0.5 rounded bg-gray-100 dark:bg-secondary-900 text-gray-500 dark:text-gray-400 uppercase border border-gray-200 dark:border-secondary-800";
                span.textContent = type;
                userTypesWrapper.appendChild(span);
            });
        } catch (e) {
            console.error("User types parse error", e);
        }
    }
}