<?php
// /resources/views/pages/registrations.php

declare(strict_types=1);

use Src\Service\AuthService;

$controller = new \Src\Controller\RegistrationsController();
$controller->index();

$registrationRows = $GLOBALS['registrationRows'] ?? '';
$isLoggedIn = AuthService::isLoggedIn();
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Registrations' => '/registrations'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Registrations</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Everyone who's registered for a division, their payment status, and contact details.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="sticky top-[84px] sm:top-[96px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-4 text-left w-full md:w-[25%]">
                            <?php $sortColumn = 'user';
                            $sortLabel = 'Registrant';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[15%]">
                            <?php $sortColumn = 'division';
                            $sortLabel = 'Division / Position';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[26%]">
                            <?php $sortColumn = 'requests';
                            $sortLabel = 'Heard About Us / Special Requests';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[11%]">
                            <?php $sortColumn = 'joined';
                            $sortLabel = 'Registered';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[11%]">
                            <?php $sortColumn = 'payment';
                            $sortLabel = 'Payment';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[70px]">
                            <?php $sortColumn = 'status';
                            $sortLabel = 'Status';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="relative px-6 py-4 text-right w-20 hidden lg:table-cell">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <?php $filterColumn = 'user';
                            $filterPlaceholder = 'Filter name, email, or phone…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'division';
                            $filterPlaceholder = 'Filter division or position…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'requests';
                            $filterPlaceholder = 'Filter source or requests…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'status';
                            $filterPlaceholder = 'paid / unpaid…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                    </tr>
                </thead>
                <tbody id="registrations-tbody" data-total="<?= (int)($GLOBALS['totalRegistrationsCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($registrationRows)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="font-medium font-sans">No registrations yet</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $registrationRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $footerCountName = 'registrations';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/registrations/view-registration-modal.php'; ?>
