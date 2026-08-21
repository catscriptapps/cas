// /resources/js/utils/messages/view-message.js

import { handleMessageFormSubmission } from './form-submit.js';

export function initViewMessage(mainContainer) {
    const drawer = document.getElementById('message-drawer');
    const backdrop = document.getElementById('drawer-backdrop');
    const panel = document.getElementById('drawer-panel');
    const closeBtn = document.getElementById('close-drawer');
    const drawerBody = document.getElementById('drawer-body');
    const drawerSubject = document.getElementById('drawer-subject');

    const toggleDrawer = (isOpen) => {
        if (isOpen) {
            drawer.classList.remove('invisible');
            setTimeout(() => {
                backdrop.classList.replace('opacity-0', 'opacity-100');
                panel.classList.replace('translate-x-full', 'translate-x-0');
            }, 10);
        } else {
            backdrop.classList.replace('opacity-100', 'opacity-0');
            panel.classList.replace('translate-x-0', 'translate-x-full');
            setTimeout(() => drawer.classList.add('invisible'), 500);
        }
    };

    /**
     * Refactored fetch logic so it can be called on init OR after a reply
     */
    const fetchAndRender = async (messageId) => {
        // Loading State
        drawerBody.innerHTML = '<div class="animate-pulse space-y-4 pt-10"><div class="h-4 bg-gray-100 rounded w-3/4"></div><div class="h-4 bg-gray-100 rounded"></div><div class="h-4 bg-gray-100 rounded w-5/6"></div></div>';

        try {
            const base = window.APP_CONFIG?.baseUrl ?? '';
            const response = await fetch(`${base}api/messages?id=${messageId}`);
            const data = await response.json();

            if (data.success) {
                drawerSubject.textContent = data.subject || 'No Subject';
                drawerBody.innerHTML = ''; 

                // Loop through the conversation thread
                data.messages.forEach((m) => {
                    // This Regex removes leading spaces/tabs from the start of the string 
                    // AND from the start of every new line (\n)
                    const cleanBody = m.body ? m.body.replace(/^\s+/gm, '').trim() : 'No content provided.';

                    const msgHtml = `
                    <div class="mb-10 pb-6 border-b last:border-0 dark:border-gray-800 ${m.is_sent ? 'pl-4 border-l-4 border-primary-500/20' : ''}">
                        <div class="mb-4 text-left">
                            <div class="text-[10px] text-primary-600 font-bold uppercase tracking-widest mb-1">
                                ${m.is_sent ? 'Your Response' : 'Message From'}
                            </div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="text-sm font-bold text-gray-900 dark:text-white">${m.name}</div>
                            </div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">${m.date}</div>
                        </div>

                        <div class="whitespace-normal text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-left block w-full">
                    ${cleanBody}
                        </div>
                    </div>
                    `;

                    drawerBody.insertAdjacentHTML('beforeend', msgHtml);
                });

                // Add the reply container at the very end
                const replySection = document.createElement('div');
                replySection.id = "reply-form-container";
                drawerBody.appendChild(replySection);
                
                // Add the Styled Reply Button
                const conversationId = data.conversation_id;
                const lastMsg = data.messages[data.messages.length - 1];

                const actionIconsHtml = `
                    <div id="reply-btn-wrapper" class="mt-6 pt-6">
                        <button type="button" id="reply-msg-btn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-secondary-900 dark:bg-white text-white dark:text-secondary-900 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 hover:bg-primary-600 hover:text-white shadow-xl shadow-black/5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                            Reply to Thread
                        </button>
                    </div>
                `;
                drawerBody.insertAdjacentHTML('beforeend', actionIconsHtml);

                document.getElementById('reply-msg-btn').addEventListener('click', () => {
                    // Hide the trigger button when the form opens
                    document.getElementById('reply-btn-wrapper').classList.add('hidden');
                    // Pass fetchAndRender as a callback to run after success
                    renderReplyForm(lastMsg.id, lastMsg.name, conversationId, () => fetchAndRender(messageId));
                });
            }
        } catch (error) {
            console.error('Drawer Error:', error);
            drawerBody.innerHTML = '<p class="text-red-500 p-6">Failed to load message content.</p>';
        }
    };

    mainContainer.addEventListener('click', async (e) => {
        const row = e.target.closest('[data-action="open-messages"]');
        if (!row || e.target.closest('button')) return;

        toggleDrawer(true);
        fetchAndRender(row.dataset.messagesId);
    });

    closeBtn?.addEventListener('click', () => toggleDrawer(false));
    backdrop?.addEventListener('click', () => toggleDrawer(false));
}

/**
 * Injects the reply form into the drawer
 */
function renderReplyForm(messageId, recipientName, conversationId, onSuccess) {
    const container = document.getElementById('reply-form-container');
    if (container.innerHTML !== '') return; 

    container.innerHTML = `
        <form id="reply-message-form" novalidate class="mt-4 pt-6 animate-in slide-in-from-bottom-2 duration-300">
            <input type="hidden" name="parent_id" value="${messageId}">
            <input type="hidden" name="conversation_id" value="${conversationId || ''}">
            <input type="hidden" name="subject" value="Re: Message">
            
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Replying to ${recipientName}</h4>
                <button type="button" id="cancel-reply-btn" class="text-[9px] font-bold text-red-500 hover:text-red-600 transition-colors uppercase tracking-tight">Cancel</button>
            </div>

            <div class="space-y-2">
                <label for="reply-text" class="block text-[10px] text-primary-600 font-bold uppercase tracking-widest ml-1">
                    Description / Body Text
                </label>
                <textarea 
                    name="message" 
                    id="reply-text" 
                    rows="4" 
                    class="w-full rounded-2xl border-gray-200 dark:border-gray-800 dark:bg-gray-950 text-sm focus:ring-primary-500 focus:border-primary-500 p-4 transition-all" 
                    placeholder="Write your response..." 
                    required></textarea>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary-700 transition-all shadow-lg shadow-primary-600/20 active:scale-95">
                    Send Response
                </button>
            </div>
        </form>
    `;

    const form = document.getElementById('reply-message-form');
    form.querySelector('textarea').focus();

    // Pass onSuccess callback to the handler
    handleMessageFormSubmission(form, 'reply', null, '#messages-tbody', onSuccess);

    document.getElementById('cancel-reply-btn').addEventListener('click', () => {
        container.innerHTML = '';
        document.getElementById('reply-btn-wrapper').classList.remove('hidden');
    });
}