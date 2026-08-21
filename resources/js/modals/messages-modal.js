// /resources/js/modals/messages-modal.js

import { Modal } from '../factories/modal-factory.js';
import { messageForm } from '../forms/message-form.js';
import { handleMessageFormSubmission } from '../utils/messages/form-submit.js';

/**
 * Message Modal Controller
 * Manages the lifecycle of the compose and reply modals.
 */

let messageModal = null;

/**
 * Logic to initialize features inside the modal once it's injected into the DOM
 */
function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Handle the API submission logic
    handleMessageFormSubmission(form, mode, modalInstance);
}

/**
 * Opens the blank Compose modal
 */
function openComposeModal() {
    if (messageModal) messageModal.destroy();

    messageModal = new Modal({
        id: 'compose-message-modal',
        title: 'New Message',
        content: messageForm({
            mode: 'add',
            formId: 'messages-add-form',
            buttonLabel: 'Send Message'
        }),
        size: 'md',
        showFooter: false,
    });

    messageModal.open();
    initFormFeatures('messages-add-form', 'add', messageModal);
}

/**
 * Opens a Reply modal (Pre-populated)
 * Triggered by the reply button inside the slide-over
 */
function openReplyModal(trigger) {
    if (!trigger?.dataset) return;
    const data = trigger.dataset;

    if (messageModal) messageModal.destroy();

    messageModal = new Modal({
        id: 'reply-message-modal',
        title: `Reply to: ${data.subject}`,
        content: messageForm({
            mode: 'reply',
            formId: 'messages-reply-form',
            recipient: data.email,
            subject: `Re: ${data.subject}`,
            buttonLabel: 'Send Reply'
        }),
        size: 'md',
        showFooter: false,
    });

    messageModal.open();
    initFormFeatures('messages-reply-form', 'reply', messageModal);
}

let listenersAttached = false;

/**
 * Global listener for message-related modal triggers
 */
export function initMessagesModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        // 1. Listen for the "New Message" button (usually in header.php)
        const addBtn = e.target.closest('#new-message-btn');
        if (addBtn) {
            e.preventDefault();
            openComposeModal();
            return;
        }

        // 2. Listen for "Reply" buttons (often inside the slide-over panel)
        const replyBtn = e.target.closest('.reply-message-btn');
        if (replyBtn) {
            e.preventDefault();
            openReplyModal(replyBtn);
        }
    });

    listenersAttached = true;
}