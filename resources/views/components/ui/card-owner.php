<?php
// /resources/views/components/ui/card-owner.php

/** @var array $item */
/** @var boolean $hasAvatar */
/** @var string $avatarUrl */
/** @var string $ownerFullName */
/** @var string $initial */
/** @var string $ownerLocation */

?>

<div class="flex items-start gap-3 mb-6">
    <div class="flex-shrink-0 w-12 h-12 rounded-2xl overflow-hidden bg-secondary-900 flex items-center justify-center shadow-inner">
        <?php if ($hasAvatar): ?>
            <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($ownerFullName) ?>" class="w-full h-full object-cover">
        <?php else: ?>
            <span class="text-xl font-black text-primary-400"><?= $initial ?></span>
        <?php endif; ?>
    </div>

    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-black text-secondary-900 dark:text-white truncate uppercase tracking-tight leading-none mb-1 mt-2">
            <?= htmlspecialchars($ownerFullName) ?>
        </h4>
        <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">
            <svg class="w-3 h-3 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
            </svg>
            <?= htmlspecialchars($ownerLocation) ?>
        </div>
        <div class="hidden">
            <?php // flex flex-wrap gap-1 // just incase i need to display it later.
            $userTypesJson = $item['user_types_json'] ?? '["Client"]';
            $userTypes = json_decode($userTypesJson, true) ?: ['Client'];
            foreach ($userTypes as $type):
            ?>
                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-gray-100 dark:bg-secondary-900 text-gray-500 dark:text-gray-400 uppercase border border-gray-200 dark:border-secondary-800">
                    <?= htmlspecialchars(trim((string)$type)) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>