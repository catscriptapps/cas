<?php
// /resources/views/pages/verify-account.php
declare(strict_types=1);

/** @var string $baseUrl */

$email = $_GET['email'] ?? '';
?>

<div id="verification-page" class="flex flex-col items-center justify-center min-h-[70vh] text-center max-w-lg mx-auto px-4">

    <div id="verifying-loader" class="space-y-6">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-primary-600"></div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white uppercase tracking-widest">Verifying Account...</h1>
        <p class="text-gray-500">Activating your profile.</p>
    </div>

    <div id="verification-success" class="hidden space-y-8">
        <div class="w-24 h-24 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <div class="space-y-4">
            <h1 class="text-4xl font-black text-secondary-950 dark:text-white">Account Verified!</h1>
            <p class="text-gray-600 dark:text-gray-400 text-lg">
                Your email <span class="font-bold text-primary-600"><?= htmlspecialchars($email) ?></span> is active.
            </p>
        </div>

        <div class="w-full pt-4">
            <a href="<?= $baseUrl ?>dashboard"
                id="verification-continue-btn"
                data-partial
                class="inline-flex items-center justify-center w-full px-12 py-5 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl text-2xl font-black shadow-2xl shadow-primary-500/20 hover:-translate-y-1 transition-all duration-300">
                Continue
            </a>
        </div>
    </div>

    <div id="verification-error" class="hidden space-y-6">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
            <span class="text-4xl">⚠️</span>
        </div>
        <h1 class="text-2xl font-bold text-red-600">Verification Failed</h1>
        <p id="error-message" class="text-gray-600 dark:text-gray-400 font-medium"></p>
        <a href="<?= $baseUrl . 'login' ?>" class="text-primary-600 font-bold hover:underline">Back to Sign In</a>
    </div>

</div>