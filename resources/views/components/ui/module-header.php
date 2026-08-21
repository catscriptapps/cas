<?php
// /resources/views/components/ui/module-header.php
declare(strict_types=1);

use Src\Service\AuthService;

/**
 * @var string $headerTitle
 * @var string $headerDesc
 * @var string $searchId
 * @var string $searchPlaceholder
 * @var string $buttonId
 * @var string $buttonText
 */
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 font-sans">
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $headerTitle ?></h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <?= $headerDesc ?>
        </p>
    </div>

    <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
        <?php if (AuthService::isLoggedIn()): ?>
            <div class="relative flex-1 md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="<?= $searchId ?>"
                    class="block w-full rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2 pl-10 pr-3 text-sm placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:text-white transition-all"
                    placeholder="<?= $searchPlaceholder ?? 'Search...' ?>">
            </div>

            <button type="button" id="<?= $buttonId ?>"
                class="shrink-0 flex items-center justify-center rounded-xl bg-secondary-400 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-secondary-500 transition-all active:scale-95 focus:ring-2 focus:ring-secondary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden sm:inline"><?= $buttonText ?></span>
            </button>
        <?php endif ?>
    </div>
</div>