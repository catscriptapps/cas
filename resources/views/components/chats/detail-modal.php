<?php
// /resources/views/components/chats/detail-modal.php

?>

<div id="chat-detail-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-secondary-900/60 backdrop-blur-sm"></div>

    <div class="absolute inset-0 md:inset-10 lg:inset-y-16 lg:inset-x-64 bg-white dark:bg-gray-950 md:rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-white/10">

        <header class="shrink-0 p-4 border-b border-gray-100 dark:border-white/5 bg-white dark:bg-gray-900 flex items-center justify-between">
            <div class="flex items-center" id="modal-user-info">
            </div>
            <button id="close-chat-modal" class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 dark:text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <div id="modal-chat-stream" class="flex-1 overflow-y-auto p-6 bg-gray-50/50 dark:bg-gray-950/50 no-scrollbar">
        </div>

        <footer class="shrink-0 p-4 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-white/5">
            <div id="modal-attachment-preview" class="flex flex-wrap gap-2 mb-3 px-1"></div>

            <form id="modal-chat-form" class="flex items-end gap-3" enctype="multipart/form-data">
                <div class="flex items-center gap-1">
                    <button type="button" id="trigger-file-input"
                        class="p-2.5 rounded-xl text-gray-400 hover:text-primary-500 hover:bg-primary-500/10 transition-all active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.5l-10.74 10.74a1.5 1.5 0 1 1-2.12-2.12l10.16-10.16" />
                        </svg>
                    </button>

                    <button type="button" id="chat-emoji-btn"
                        class="p-2.5 rounded-xl text-gray-400 hover:text-yellow-500 hover:bg-yellow-500/10 transition-all active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                        </svg>
                    </button>
                </div>

                <textarea name="message_text" id="modal-message-input" rows="1"
                    class="flex-1 bg-gray-100 dark:bg-white/5 border-none rounded-2xl px-4 py-2.5 text-sm dark:text-white resize-none focus:ring-1 focus:ring-primary-500/50 transition-all no-scrollbar"
                    placeholder="Write a message..."></textarea>

                <button type="submit"
                    class="bg-primary-600 hover:bg-primary-500 active:scale-95 p-2.5 rounded-xl text-white transition-all shadow-lg shadow-primary-500/20 group h-[42px] w-[42px] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 transition-transform group-hover:rotate-12 group-hover:-translate-y-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
        </footer>
    </div>
</div>