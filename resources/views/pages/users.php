<?php
// /resources/views/pages/users.php

declare(strict_types=1);

use Src\Service\AuthService;

$controller = new \Src\Controller\UsersController();
$controller->index();

$userRows = $GLOBALS['userRows'] ?? '';
$isAdmin = AuthService::isAdmin();
// A Company Admin can add Inspectors to their own company's roster from
// here too (forced server-side in UsersController::save()) -- the modal
// itself swaps to a locked-to-Inspector form for them, see users-modal.js.
$canAddUser = $isAdmin || AuthService::isCompanyAdmin();
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Users' => '/users'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Users</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                A list of all users including their location, account status, and professional roles.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <?php if ($canAddUser): ?>
                <button type="button" id="add-user-btn" data-tooltip="Add User"
                    class="shrink-0 flex items-center justify-center rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden xs:inline md:inline">Add User</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="sticky top-[58px] sm:top-[62px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-4 text-left w-full md:w-[30%]">
                            <?php $sortColumn = 'user';
                            $sortLabel = 'User';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[20%]">
                            <?php $sortColumn = 'city';
                            $sortLabel = 'Location';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[20%]">
                            <?php $sortColumn = 'role';
                            $sortLabel = 'Role';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[15%]">
                            <?php $sortColumn = 'joined';
                            $sortLabel = 'Activity';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[100px]">
                            <?php $sortColumn = 'status';
                            $sortLabel = 'Status';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="relative px-6 py-4 text-right w-24 hidden lg:table-cell">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <?php $filterColumn = 'user';
                            $filterPlaceholder = 'Filter name or email…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'city';
                            $filterPlaceholder = 'Filter location…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'role';
                            $filterPlaceholder = 'Filter role…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'status';
                            $filterPlaceholder = 'Filter status…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                    </tr>
                </thead>
                <tbody id="users-tbody" data-total="<?= (int)($GLOBALS['totalUsersCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($userRows)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="font-medium font-sans">No users found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $userRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $footerCountName = 'users';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/users/view-user-modal.php'; ?>