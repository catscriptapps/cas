<?php
// /resources/views/pages/messages.php

declare(strict_types=1);

use Src\Service\AuthService;

// Admin-only page -- the topbar icon that links here only ever renders for
// an admin (see layout-topbar.php), but this guards direct navigation too.
if (!AuthService::isAdmin()) {
    include __DIR__ . '/auth-required.php';
    return;
}

$controller = new \Src\Controller\MessagesController();
$controller->index();

$messageRows = $GLOBALS['messageRows'] ?? '';
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Messages' => '/messages'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "Messages" + its NavigationConfig summary (see
    // layout-header.php).
    ?>

    <div class="flex items-center gap-2" id="messages-view-tabs">
        <button type="button" data-view="inbox" class="messages-view-tab-btn px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all bg-primary-500 text-white shadow-sm">
            Inbox
        </button>
        <button type="button" data-view="archived" class="messages-view-tab-btn px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
            Archived
        </button>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="sticky top-0 z-[30] shadow-sm rounded-t-2xl overflow-clip">
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-4 text-left w-full lg:w-[28%]">
                            <?php $sortColumn = 'sender';
                            $sortLabel = 'Sender';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell">
                            <?php $sortColumn = 'subject';
                            $sortLabel = 'Subject';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[180px]">
                            <?php $sortColumn = 'date';
                            $sortLabel = 'Received';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[110px]">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</span>
                        </th>
                        <th class="relative px-6 py-4 text-right w-28 hidden lg:table-cell">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <?php $filterColumn = 'message';
                            $filterPlaceholder = 'Filter name, email, subject…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'status';
                            $filterPlaceholder = 'read / unread…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                    </tr>
                </thead>
                <tbody id="messages-tbody" data-total="<?= (int)($GLOBALS['totalMessagesCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($messageRows)): ?>
                        <tr class="empty-state-row">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    <p class="font-medium font-sans">No messages</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $messageRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $footerCountName = 'messages';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/messages/view-message-modal.php'; ?>
