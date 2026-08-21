<?php
// /resources/views/pages/history.php

declare(strict_types=1);

use Src\Service\AuthService;

/** @var \App\Models\User|null $currentUser */
if ($currentUser) {
    $controller = new \Src\Controller\RecentActivitiesController();
    $controller->index();

    $historyRows = $GLOBALS['historyRows'] ?? '';
    $historyCount = $GLOBALS['historyCount'] ?? 0;
?>

    <div class="space-y-6 max-w-full py-10">
        <?php
        $breadcrumbs = ['History' => '/history'];
        include __DIR__ . '/../components/ui/breadcrumbs.php';
        ?>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">History</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Track system changes, user actions, and security events across the platform.
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
            <div class="w-full">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                    <thead class="sticky top-[58px] sm:top-[62px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-4 text-left w-full md:w-[40%] font-sans">
                                <?php $sortColumn = 'user';
                                $sortLabel = 'User / Action';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="px-6 py-4 text-left hidden md:table-cell md:w-[20%] font-sans">
                                <?php $sortColumn = 'entity_type';
                                $sortLabel = 'Entity Type';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="px-6 py-4 text-left hidden md:table-cell md:w-[20%] font-sans">
                                <?php $sortColumn = 'date';
                                $sortLabel = 'Timestamp';
                                include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                            </th>
                            <th class="relative px-6 py-4 text-right w-24 hidden md:table-cell">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                        <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                            <th class="px-6 py-2.5">
                                <?php $filterColumn = 'user';
                                $filterPlaceholder = 'Filter user or action…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden md:table-cell">
                                <?php $filterColumn = 'entity_type';
                                $filterPlaceholder = 'Filter type…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden md:table-cell">
                                <?php $filterColumn = 'date';
                                $filterPlaceholder = 'Filter date…';
                                include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                            </th>
                            <th class="px-6 py-2.5 hidden md:table-cell"></th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody" data-total="<?= (int)$historyCount ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        <?php if (empty($historyRows)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-sans">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="font-medium">No activity history found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?= $historyRows ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $footerCountName = 'history';
            include __DIR__ . '/../components/ui/footer-count.php';
            ?>
        </div>
    </div>

<?php include __DIR__ . '/../components/history/archive-modal.php';
} else {
    include __DIR__ . '/auth-required.php';
}
