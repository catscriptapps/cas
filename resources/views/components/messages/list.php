<?php
// /resources/components/messages/list.php
declare(strict_types=1);

/** @var \Illuminate\Support\Collection $items */
/** @var string $currentFolder */
?>
<div id="messages-container" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
    <?php if ($items->isEmpty()): ?>
        <div class="empty-state flex flex-col items-center justify-center py-24 text-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Empty Folder</h3>
            <p class="text-sm text-gray-500 max-w-xs mx-auto">Looks like there are no messages in your <?= htmlspecialchars($currentFolder) ?> at the moment.</p>
        </div>
    <?php else: ?>
        <div id="messages-tbody" class="divide-y divide-gray-100 dark:divide-gray-800">
            <?php foreach ($items as $item): ?>
                <?php include __DIR__ . '/table-row.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>