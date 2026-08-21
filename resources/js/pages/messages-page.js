// /resources/js/pages/messages-page.js

import { initSidebarNav } from "../utils/messages/sidebar-nav.js";
import { initMessageActions } from "../utils/messages/message-actions.js";
import { initViewMessage } from "../utils/messages/view-message.js";
import { initMessagesModal } from "../modals/messages-modal.js";
import { initDeleteMessage } from "../utils/messages/delete-message.js";
import { AnimationEngine } from '../utils/animations';

/**
 * Initialize the Messages page JS.
 */
export function init() {
    AnimationEngine.refresh();
    
    const mainContainer = document.querySelector('main.lg\\:col-span-9');
    const sidebar = document.querySelector('aside nav');

    if (!mainContainer || !sidebar) return;

    // 1. Handle folder switching
    initSidebarNav(sidebar, mainContainer);

    // 2. Handle row clicks (Open)
    initViewMessage(mainContainer);

    // 3. Handle hover actions (Delete/Archive)
    initMessageActions(mainContainer);

    // 4. The modal for coposing and replying messages
    initMessagesModal();

    // 5. Delete messages
    initDeleteMessage('#messages-tbody');
}