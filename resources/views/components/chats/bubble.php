<?php
// /resources/views/components/chats/bubble.php

/**
 * Variables passed from ChatsController:
 * @var array $item (id, message_text, created_at, is_read, is_me, message_type, attachment_url)
 */

$fullMediaUrl = !empty($item['attachment_url']) ? htmlspecialchars($item['attachment_url']) : null;
$encodedId = App\Utils\IdEncoder::encode((int)$item['id']);
?>

<div id="msg-<?= $encodedId ?>" class="flex w-full mb-4 <?= $item['is_me'] ? 'justify-end' : 'justify-start' ?>">
    <div class="relative max-w-[85%] md:max-w-[70%] group">

        <?php if ($item['is_me']): ?>
            <div id="confirm-<?= $encodedId ?>"
                class="hidden absolute inset-0 z-20 flex items-center justify-center bg-slate-900 rounded-2xl border-2 border-orange-500 shadow-xl min-w-[110px] animate-in fade-in zoom-in duration-200">

                <div class="flex flex-col items-center space-y-1">
                    <span class="text-[8px] font-black text-white/40 uppercase tracking-widest">Delete?</span>
                    <div class="flex items-center space-x-2">
                        <button class="confirm-delete-btn p-1.5 bg-red-600 hover:bg-red-700 text-white rounded-full transition-all active:scale-90"
                            data-id="<?= $encodedId ?>" title="Confirm">
                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>

                        <button class="cancel-delete-btn p-1.5 bg-white/10 hover:bg-white/20 text-white rounded-full transition-all active:scale-90"
                            data-id="<?= $encodedId ?>" title="Cancel">
                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button"
                class="trigger-delete-btn absolute -left-10 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all duration-200"
                title="Delete message">
                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        <?php endif; ?>

        <div class="px-4 py-3 rounded-2xl shadow-sm 
            <?= $item['is_me']
                ? 'bg-orange-500 text-white rounded-tr-none'
                : 'bg-slate-800 text-white rounded-tl-none' ?>">

            <?php if ($item['message_type'] === 'text'): ?>
                <p class="text-sm leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($item['message_text']) ?></p>
            <?php endif; ?>

            <?php if ($item['message_type'] === 'image' && $fullMediaUrl): ?>
                <div class="mb-2 overflow-hidden rounded-lg">
                    <img src="<?= $fullMediaUrl ?>" class="object-cover w-full max-h-64 cursor-zoom-in" alt="Sent image">
                </div>
                <?php if (!empty($item['message_text'])): ?>
                    <p class="text-sm opacity-90"><?= htmlspecialchars($item['message_text']) ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="flex items-center justify-end mt-1 space-x-1 opacity-60">
                <span class="text-[10px] uppercase font-bold tracking-tighter">
                    <?= $item['created_at']->format('H:i') ?>
                </span>
                <?php if ($item['is_me']): ?>
                    <svg class="w-3 h-3 <?= $item['is_read'] ? 'text-blue-300' : 'text-white' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        <?php if ($item['is_read']): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 13l4 4L23 7" class="-ml-2"></path>
                        <?php endif; ?>
                    </svg>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    #gonachi-lightbox .animate-in {
        animation: fadeIn 0.3s ease-out forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>