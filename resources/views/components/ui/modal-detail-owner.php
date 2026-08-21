<?php
// /resources/views/components/ui/modal-detail-owner.php

/** @var string $modalDetailOwnerId */
/** @var string $modalDetailOwnerTitle */
?>

<div class="flex items-center gap-4 p-4 bg-gray-50/50 dark:bg-secondary-900/10 rounded-[1.5rem] border border-gray-100 dark:border-secondary-800 shadow-sm">
    <div id="view-<?= $modalDetailOwnerId ?>-owner-avatar-container" class="flex-shrink-0 w-12 h-12 rounded-xl overflow-hidden bg-secondary-900 flex items-center justify-center shadow-inner">
        <span id="view-<?= $modalDetailOwnerId ?>-owner-initial" class="text-xl font-black text-primary-400"></span>
    </div>

    <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-4 min-w-0">

        <div class="min-w-0">
            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-primary-400 mb-1 opacity-70"><?= $modalDetailOwnerTitle ?></p>
            <h4 id="view-<?= $modalDetailOwnerId ?>-owner-name" class="text-base font-black text-secondary-900 dark:text-white truncate uppercase tracking-tight leading-tight mb-0.5">
                ---
            </h4>
            <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                <svg class="w-3 h-3 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                <span id="view-<?= $modalDetailOwnerId ?>-owner-location">---</span>
            </div>
        </div>

        <div id="view-<?= $modalDetailOwnerId ?>-user-types-wrapper" class="flex flex-wrap sm:justify-end gap-1.5 shrink-0">
        </div>
    </div>
</div>