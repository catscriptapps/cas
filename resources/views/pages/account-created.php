<?php
// /resources/views/pages/account-created.php

declare(strict_types=1);

/**
 * @var string $baseUrl
 */

// Threaded through from a "Subscribe to Module" registration prompt, so the
// Sign In click below lands the landlord straight back where they meant to go.
$returnTo = trim((string)($_GET['redirect'] ?? '')) ?: null;
?>

<div class="min-h-screen bg-white dark:bg-black text-slate-800 dark:text-slate-100 font-sans transition-colors duration-300 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-center p-8 max-w-lg mx-auto bg-slate-50/50 dark:bg-slate-900/50 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl">
        <div class="mx-auto w-24 h-24 mb-6 flex items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg shadow-primary-500/40">
            <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight uppercase">
            Account Created
        </h1>
        <p class="mt-4 text-slate-600 dark:text-slate-400 font-medium leading-relaxed">
            Thank you for registering your corporate entity. Your account is now active and ready for use. You can sign in to access your dashboard and begin managing your properties.
        </p>
        <div class="mt-8">
            <a href="<?= $baseUrl ?>login" data-login-button <?= $returnTo ? 'data-return-to="' . htmlspecialchars($returnTo) . '"' : '' ?> title="Authenticate environment identity credentials" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-500 dark:hover:bg-primary-600 dark:text-slate-950 font-black uppercase text-sm tracking-wider transition-all duration-200 shadow-lg shadow-primary-500/30 transform hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                Sign In to Your Portal
            </a>
        </div>
    </div>
</div>