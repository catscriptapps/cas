<?php
// /resources/components/messages/slide-over.php
?>

<style>
    /* Custom utility to hide scrollbar while maintaining functionality */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }
</style>

<div id="message-drawer" class="fixed inset-0 z-50 invisible overflow-hidden" role="dialog" aria-modal="true">
    <div id="drawer-backdrop" class="absolute inset-0 bg-gray-500/75 transition-opacity opacity-0"></div>

    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div id="drawer-panel" class="w-screen max-w-2xl transform transition-transform translate-x-full bg-white dark:bg-gray-900 shadow-2xl font-sans">
            <div class="flex h-full flex-col overflow-y-scroll no-scrollbar py-6">
                <div class="px-4 sm:px-6 flex items-start justify-between">
                    <div class="pr-8">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight" id="drawer-subject">Loading...</h2>
                    </div>
                    <button id="close-drawer" class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div id="drawer-body" class="relative mt-6 flex-1 px-4 sm:px-6">
                </div>
            </div>
        </div>
    </div>
</div>