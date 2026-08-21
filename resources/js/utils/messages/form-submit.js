// /resources/js/utils/messages/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Maps form data to an API payload for Messages
 */
function getPayload(form, mode) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Determine if this is an outbound message (Reply or Admin Compose)
    // vs an inbound message (Guest Contact Form)
    const isOutbound = (mode === 'reply' || mode === 'compose');

    return {
        action: 'create',
        parent_id: data.parent_id || null,
        conversation_id: data.conversation_id || null,
        full_name: isOutbound ? 'System User' : (data.full_name || 'Guest'), 
        email: data.email?.trim(),
        subject: data.subject?.trim(),
        message: data.message?.trim(),
        
        // Dynamic flags
        is_sent: isOutbound, 
        is_read: isOutbound  // Only true if we are the ones sending it
    };
}

/**
 * Handles Compose/Reply message form submission
 * Added onSuccess callback to trigger live UI refreshes (like the Message Drawer)
 */
export function handleMessageFormSubmission(
    form, 
    mode, 
    modalInstance, 
    tableSelector = '#messages-tbody',
    onSuccess = null
) {
    if (form._messageFormListenerAttached) return;
    form._messageFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    let apiMsg = form.querySelector('.api-message');

    if (!apiMsg) {
        apiMsg = document.createElement('div');
        apiMsg.className = 'api-message mt-4 transition-all duration-300';
        form.appendChild(apiMsg);
    }

    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validator.validateForEmptyFields(e)) return;

        // UI State: Loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner; 
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form, mode);
            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            
            const response = await fetch(`${baseUrl}api/messages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                // 1. --- REPLY MODE SPECIFIC HANDLING ---
                if (mode === 'reply') {
                    apiMsg.innerHTML = `
                        <div class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl text-[10px] font-black uppercase tracking-widest text-center border border-green-100 dark:border-green-900/30 p-4">
                            Reply sent successfully!
                        </div>`;
                    
                    // Hide the button to prevent double-submits
                    submitBtn.style.display = 'none';

                    // Execute callback (e.g., refresh the drawer thread)
                    if (typeof onSuccess === 'function') {
                        onSuccess();
                    }

                    // Remove the form container after the user has seen the success message
                    setTimeout(() => {
                        const container = form.closest('#reply-form-container');
                        if (container) container.innerHTML = '';
                    }, 1500);
                    
                    return; 
                }

                // 2. --- TABLE UPDATE LOGIC (For Inbox/Sent folders) ---
                const currentFolder = new URLSearchParams(window.location.search).get('folder') || 'inbox';
                const tbody = document.querySelector(tableSelector);
                
                if (tbody && currentFolder === 'sent' && result.rowHtml) {
                    const emptyStateRow = tbody.querySelector('.empty-state-row') || 
                                         tbody.querySelector('td[colspan]')?.closest('tr');
                    if (emptyStateRow) emptyStateRow.remove();

                    tbody.insertAdjacentHTML('afterbegin', result.rowHtml);
                    updateCount('message', tableSelector, '#messages-count');
                }

                // 3. --- GENERAL SUCCESS FEEDBACK ---
                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">
                        ${result.message || 'Message sent successfully!'}
                    </div>
                `;

                submitBtn.style.display = 'none'; 
                
                // 4. --- CLEANUP & CLOSE ---
                if (typeof onSuccess === 'function' && mode !== 'reply') {
                    onSuccess();
                }

                setTimeout(() => {
                    if (modalInstance && typeof modalInstance.close === 'function') {
                        modalInstance.close();
                    }
                }, 800);

            } else {
                // Error UI
                apiMsg.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">
                        ${result.message || 'Check your input'}
                    </div>`;
            }

        } catch (err) {
            console.error('Submission Error:', err);
            apiMsg.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2 text-center">
                    Unexpected error. Please try again.
                </div>`;
        } finally {
            if (submitBtn.style.display !== 'none') {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}