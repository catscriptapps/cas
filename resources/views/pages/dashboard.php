<?php
// /resources/views/pages/dashboard.php

declare(strict_types=1);

/** @var bool $isLoggedIn */
/** @var string $appName */
/** @var string $baseUrl */

use Src\Config\NavigationConfig;
use Src\Controller\RecentActivitiesController;
use Src\Controller\UserTypesController;
use App\Models\User;

// Anyone who isn't a resolvable backend User (staff/admin) has no business
// seeing the internal activity feed and workspace links below -- this also
// covers a stale session (e.g. one whose user row was since deleted), where
// AuthService::isLoggedIn() can still be true from the session flag alone
// even though $currentUser is null.
if (!$currentUser) {
    include __DIR__ . '/auth-required.php';
    return;
}

// Fetch data
$navLinks = NavigationConfig::getNavLinks((bool)$isLoggedIn);
$icons = NavigationConfig::getIcons();

$totalUsers = User::count();

/** @var \App\Models\User|null $currentUser */
$userName = $currentUser->full_name ?? 'there';
$recentActivities = RecentActivitiesController::latest(10);

// Role badge -- shown big and prominent in the hero header per user request.
if (!isset($GLOBALS['allUserTypes'])) {
    $types = UserTypesController::list();
    $GLOBALS['allUserTypes'] = [];
    foreach ($types as $t) {
        $GLOBALS['allUserTypes'][$t->user_type_id] = $t->user_type;
    }
}
$roleLabel = $GLOBALS['allUserTypes'][$currentUser->user_type_id ?? 0] ?? 'User';

// Maps a RecentActivity::category value (see the logActivity() call sites
// across the controllers) to an icon. Reuses the shared nav icons where one
// already exists, and defines a small inline icon for the rest so every
// real category in use actually gets a matching icon instead of falling
// through to a generic bell.
$getCategoryIcon = function ($category) use ($icons) {
    $inline = [
        'Auth' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>',
        'Tenants' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>',
        'Properties' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>',
        'Access Tokens' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 11-12 0 6 6 0 0112 0zM2.25 21a8.966 8.966 0 015.06-8.006" /></svg>',
        'Message' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>',
        'General' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>',
    ];

    $iconMarkup = $icons[$category] ?? ($category === 'RentalApplication' ? ($icons['Rental Applications'] ?? null) : null);
    $iconMarkup = $iconMarkup ?? $inline[$category] ?? $inline['General'];

    // Normalize sizing regardless of source -- the shared nav icons come in
    // at h-6/w-6, so strip whatever class is already there before applying ours.
    $iconMarkup = preg_replace('/\sclass="[^"]*"/i', '', $iconMarkup, 1);
    return preg_replace('/(<svg[^>]*)(>)/i', '$1 class="w-5 h-5"$2', $iconMarkup, 1);
};
?>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

    .glow-primary {
        box-shadow: 0 0 20px rgba(252, 131, 43, 0.2);
    }

    .group:hover .glow-primary-hover {
        box-shadow: 0 0 30px rgba(252, 131, 43, 0.4);
    }
</style>

<title>Dashboard | <?= htmlspecialchars($appName ?? 'PMB') ?></title>

<div class="space-y-10 font-sans pb-10">

    <div class="animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
        <?php include __DIR__ . '/../components/dashboard/hero-header.php'; ?>
    </div>

    <div class="space-y-4">
        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 dark:text-gray-500 px-2 animate-in fade-in delay-500">
            Workspace Hub
        </h2>
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php
            $delay = 100;
            // Updated loop structure to correctly extract the sub-configuration attributes
            foreach ($navLinks as $name => $appConfig):
                if (in_array($name, ['Home', 'About', 'Contact', 'Dashboard'])) continue;
                $url = $appConfig['url'] ?? '#';
            ?>
                <a href="<?= $url ?>"
                    data-partial
                    data-title="<?= htmlspecialchars($appConfig['title'] ?? $name) ?>"
                    data-summary="<?= htmlspecialchars($appConfig['summary'] ?? '') ?>"
                    style="animation-delay: <?= $delay ?>ms"
                    class="group animate-in fade-in zoom-in-95 duration-700 fill-mode-both relative overflow-hidden rounded-[2.5rem] bg-primary-100 dark:bg-black p-8 shadow-sm border border-gray-100 dark:border-gray-800 transition-all hover:shadow-[0_20px_60px_-15px_rgba(252,131,43,0.3)] hover:-translate-y-2 hover:border-primary-400/50">

                    <div class="flex items-center justify-between relative z-10">
                        <div class="rounded-2xl bg-primary-50 dark:bg-primary-900/20 p-4 text-primary-600 dark:text-primary-400 group-hover:bg-primary-400 group-hover:text-white transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 glow-primary-hover">
                            <div class="w-6 h-6"><?= $icons[$name] ?? '' ?></div>
                        </div>
                        <div class="p-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-4 transition-all duration-300">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                    </div>

                    <div class="mt-8 relative z-10">
                        <h3 class="text-xl font-black text-secondary-900 dark:text-white"><?= htmlspecialchars($name) ?></h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                            </span>
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-black">Active Module</p>
                        </div>
                    </div>

                    <div class="absolute -bottom-6 -right-6 text-gray-50 dark:text-white/5 opacity-0 group-hover:opacity-100 transition-all duration-700 group-hover:rotate-0 rotate-45">
                        <div class="scale-[4.0]"><?= $icons[$name] ?? '' ?></div>
                    </div>
                </a>
            <?php $delay += 100;
            endforeach; ?>

        </div>
    </div>

    <div class="space-y-4 animate-in fade-in slide-in-from-bottom-10 duration-1000 delay-700 fill-mode-both">
        <div class="flex items-center justify-between px-2">
            <div>
                <h2 class="text-xl font-black text-secondary-900 dark:text-white tracking-tight">Recent Activity</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium mt-0.5">Latest actions across your ecosystem</p>
            </div>
            <a href="<?= $baseUrl ?>history" data-partial class="group flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-200 dark:border-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 transition-all">
                View All
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50 dark:divide-gray-800">
                <?php if (!$recentActivities || $recentActivities->isEmpty()): ?>
                    <div class="py-16 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-300 dark:text-gray-600 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">No recent activity yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity):
                        $category = $activity->entity_type ?? 'General';
                        // Split camelCase entity_type values (e.g. "RentalApplication")
                        // into readable words for the badge label.
                        $categoryLabel = trim((string)preg_replace('/(?<!^)([A-Z])/', ' $1', $category));
                    ?>
                        <div class="group px-4 sm:px-5 py-3.5 hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors flex items-center gap-4">
                            <div class="h-10 w-10 shrink-0 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors">
                                <?= $getCategoryIcon($category) ?>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate"><?= htmlspecialchars($activity->action ?? 'Action') ?></p>
                                <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?= htmlspecialchars($categoryLabel) ?></span>
                                    <span><?= htmlspecialchars($activity->user->full_name ?? 'System') ?></span>
                                </div>
                            </div>

                            <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                <?= $activity->created_at ? $activity->created_at->diffForHumans() : 'Just now' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>