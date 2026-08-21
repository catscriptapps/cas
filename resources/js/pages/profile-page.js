// /resources/js/pages/profile-page.js

import { initProfileModal } from '../modals/profile-modal.js';
import { initProfileAvatar } from '../utils/profile/profile-avatar.js';
import { initChangePassword } from '../utils/profile/change-password.js';
import { AnimationEngine } from '../utils/animations';

/**
 * Initialize the Profile page JS with CatScript animations.
 */
export function init() {
    // 1. Fire AOS to animate the new profile cards and hero section
    AnimationEngine.refresh();

    if (document.querySelector('[data-action="edit-user-profile"]')) {
        initProfileModal();
        initProfileAvatar();
    }

    initChangePassword();
}
