<?php
// /resources/views/components/dashboard/hero-header.php

declare(strict_types=1);

/** @var string $userName */
/** @var string $appName */
/** @var string $assetBase */
/** @var string|null $pageIcon */
/** @var \App\Models\User|null $currentUser */

/**
 * @var string $userName
 * @var int $totalLandlords
 * @var int $totalUsers
 * @var string $roleLabel
 */

// Inherits $userName, $appName, $currentUser, $roleLabel, etc. from dashboard.php scope
$hasAvatar = !empty($currentUser->avatar_url ?? null);
$avatarUrl = $hasAvatar ? $assetBase . 'images/uploads/avatars/' . $currentUser->avatar_url : '';
$userInitial = strtoupper(substr($currentUser->first_name ?? $userName, 0, 1)) ?: 'U';
?>

<div class="w-screen relative left-1/2 -translate-x-1/2 overflow-hidden bg-primary-100 dark:bg-gray-900/50 py-8 lg:py-12 border-y border-gray-200/60 dark:border-white/5 transition-colors duration-300 mt-6 shadow-sm">
    <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-transparent to-secondary-500/5 opacity-100"></div>
    <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-400/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-secondary-400/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-5xl mx-auto px-4 md:px-6 relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-6 w-full">
            <div class="hidden lg:flex w-16 h-16 shrink-0 items-center justify-center rounded-2xl overflow-hidden bg-gradient-to-br from-primary-500 to-secondary-600 border border-gray-200 dark:border-white/10 shadow-sm backdrop-blur-md animate-float">
                <?php if ($hasAvatar): ?>
                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($userName) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <span class="text-3xl font-black text-white tracking-tighter"><?= htmlspecialchars($userInitial) ?></span>
                <?php endif; ?>
            </div>

            <div class="flex-1 flex flex-col md:flex-row md:items-center md:justify-between gap-6 w-full">
                <div class="max-w-xl">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <h1 class="text-3xl lg:text-4xl font-black tracking-tight text-gray-900 dark:text-white leading-tight">
                            Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-500 dark:from-primary-400 dark:to-secondary-400"><?= htmlspecialchars($userName) ?></span>
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-secondary-600 dark:bg-secondary-500 text-white text-sm font-black uppercase tracking-wider shadow-md shadow-secondary-500/30 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= htmlspecialchars($roleLabel) ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                        Here's a snapshot of the league's current state.
                    </p>
                </div>

                <div class="flex items-center gap-4 shrink-0 self-start md:self-auto ml-auto">
                    <div class="px-5 py-2.5 rounded-2xl bg-white dark:bg-white/5 border border-gray-200/60 dark:border-white/10 shadow-sm text-center min-w-[5rem]">
                        <span class="text-2xl font-black text-secondary-600 dark:text-secondary-400 block leading-none"><?= $totalUsers ?></span>
                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider block mt-1">Users</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>