<?php
// /resources/views/components/chats/detail-modal-header.php

/**
 * @var string $displayName
 * @var string|null $avatarUrl
 * @var string $initial
 * @var string $location
 */
?>
<div class="flex items-center">
    <?php if ($avatarUrl): ?>
        <img src="<?= htmlspecialchars($avatarUrl) ?>" class="w-10 h-10 rounded-full mr-3 object-cover border border-gray-100 dark:border-white/10" alt="<?= $displayName ?>">
    <?php else: ?>
        <div class="w-10 h-10 rounded-full bg-secondary-900 text-white flex items-center justify-center font-black mr-3 shadow-inner text-xs">
            <?= $initial ?>
        </div>
    <?php endif; ?>

    <div>
        <h2 class="text-xs font-black text-secondary-900 dark:text-white leading-none uppercase tracking-tighter"><?= $displayName ?></h2>
        <div class="flex items-center gap-3 mt-1.5">
            <div class="flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                <span class="text-[8px] text-gray-400 font-black uppercase tracking-widest">Online</span>
            </div>
            <div class="flex items-center gap-1 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5">
                    <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                </svg>
                <span class="text-[8px] font-black uppercase tracking-widest"><?= htmlspecialchars($location) ?></span>
            </div>
        </div>
    </div>
</div>