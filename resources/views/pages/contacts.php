<?php
// /resources/views/pages/contacts.php

declare(strict_types=1);

use Src\Service\AuthService;

$controller = new \Src\Controller\ContactsController();
$controller->index();

$contactRows = $GLOBALS['contactRows'] ?? '';
$canManage = AuthService::isLoggedIn();
?>

<div class="space-y-6 max-w-full py-10">
    <?php
    $breadcrumbs = ['Contacts' => '/contacts'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Contact Directory</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                League officials, timekeepers, and emergency contacts.
            </p>
        </div>

        <?php if ($canManage): ?>
            <button type="button" id="add-contact-btn"
                class="shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Contact
            </button>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="sticky top-[84px] sm:top-[96px] z-[30] shadow-sm rounded-t-2xl overflow-clip">
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-4 text-left w-full lg:w-[30%]">
                            <?php $sortColumn = 'contact';
                            $sortLabel = 'Name / Organization';
                            include __DIR__ . '/../components/ui/sortable-th.php'; ?>
                        </th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[20%]">Leagues</th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[15%]">Phone</th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[17%]">Role</th>
                        <th class="px-6 py-4 text-left hidden lg:table-cell w-[12%]">Emergency</th>
                        <th class="relative px-6 py-4 text-right w-24 hidden lg:table-cell">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <?php $filterColumn = 'contact';
                            $filterPlaceholder = 'Filter name, org, or email…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'leagues';
                            $filterPlaceholder = 'Filter leagues…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'role';
                            $filterPlaceholder = 'Filter role…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <?php $filterColumn = 'emergency';
                            $filterPlaceholder = 'yes / no…';
                            include __DIR__ . '/../components/ui/table-filter-input.php'; ?>
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                    </tr>
                </thead>
                <tbody id="contacts-tbody" data-total="<?= (int)($GLOBALS['totalContactsCount'] ?? 0) ?>" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($contactRows)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="font-medium font-sans">No contacts found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $contactRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $footerCountName = 'contacts';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/contacts/view-contact-modal.php'; ?>
