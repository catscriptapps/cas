<?php
// /resources/views/partials/layout-topbar.php

use Src\Config\NavigationConfig;
use Src\Service\AuthService;

/** @var bool $isLoggedIn */
/** @var string $baseUrl */
/** @var string $assetBase */
/** @var string $appName */
/** @var object|null $currentUser */

// Centralize display logic by calling the new method in NavigationConfig.
// The extract() function imports the 'displayName' and 'initial' keys
// from the returned array into the current scope.
extract(NavigationConfig::getUserDisplayInfo());

// --- Nav link resolution (moved here from layout-header-nav.php, along with
// the logo and the nav itself -- the topbar is now the sole home for both) ---
$navLinks = NavigationConfig::getNavLinks($isLoggedIn);

$currentUrlTrimmed = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '/');
?>

<div class="fixed top-0 left-0 w-full bg-black text-slate-200 px-4 sm:px-6 lg:px-8 py-2 text-sm sm:text-base flex justify-between items-center border-b-2 border-gray-700 min-h-[76px] sm:min-h-[88px] transition-colors duration-200 shadow-xl select-none z-[9999]"
    x-data="{ mobileMenuOpen: false }"
    x-effect="document.body.style.overflow = mobileMenuOpen ? 'hidden' : ''">

    <!-- Logo, far left -->
    <div class="flex items-center shrink-0">
        <a href="<?= $baseUrl ?>" data-partial data-title="Home" class="flex items-center gap-3 min-w-0 transition-opacity hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-amber-500 rounded-lg">
            <img src="<?= $assetBase ?>images/logo/logo.png" alt="<?= htmlspecialchars($appName) ?>" class="h-16 w-16 sm:h-20 sm:w-20 object-contain shrink-0">
            <span class="hidden sm:inline-block font-black text-white tracking-tight text-base lg:text-lg whitespace-nowrap truncate">
                <?= htmlspecialchars($appName) ?>
            </span>
        </a>
    </div>

    <!-- Nav (desktop) + icon cluster + auth + mobile toggle, far right -->
    <div class="flex items-center gap-3 sm:gap-5 font-bold">

        <nav class="hidden 2xl:flex items-center gap-5 xl:gap-6 text-sm font-bold text-slate-200">
            <?php foreach ($navLinks as $name => $config): ?>
                <?php
                // Detect if this element represents the Home link
                $isHomeItem = (strtolower($name) === 'home' || rtrim($config['url'], '/') === rtrim($baseUrl, '/'));
                $targetUrl = $isHomeItem ? $baseUrl : $config['url'];
                ?>

                <?php if (isset($config['children'])): ?>
                    <div class="relative group flex items-center h-full cursor-pointer py-2">
                        <?php
                        $isActive = ($currentUrlTrimmed === rtrim($targetUrl, '/'));
                        $desktopClasses = $isActive
                            ? "text-amber-400 group-hover:text-amber-300 transition-colors flex items-center gap-1.5 focus:outline-none focus:underline"
                            : "text-slate-200 hover:text-amber-300 transition-colors flex items-center gap-1.5 focus:outline-none focus:underline";
                        ?>
                        <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" class="<?= $desktopClasses ?>">
                            <span><?= $name ?></span>
                            <svg class="w-3.5 h-3.5 transform group-hover:rotate-180 transition-transform duration-200 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>

                        <div class="absolute top-full right-0 min-w-[240px] bg-slate-950 dark:bg-black border-2 border-slate-800 dark:border-slate-900 rounded-xl shadow-2xl py-3 opacity-0 scale-95 pointer-events-none group-hover:opacity-100 group-hover:scale-100 group-hover:pointer-events-auto transition-all duration-150 z-50">
                            <?php foreach ($config['children'] as $childName => $childConfig): ?>
                                <?php
                                $isChildActive = ($currentUrlTrimmed === rtrim($childConfig['url'], '/'));
                                // Static file links (PDFs) open in a new tab and skip the SPA
                                // fetch entirely -- bindPartialLinks() already ignores clicks on
                                // any [target="_blank"] link, so this alone is enough; no need to
                                // also drop data-partial.
                                $childTargetAttr = isset($childConfig['target']) ? ' target="' . htmlspecialchars($childConfig['target']) . '"' : '';
                                ?>
                                <a href="<?= $childConfig['url'] ?>"<?= $childTargetAttr ?> data-partial data-title="<?= htmlspecialchars($childConfig['title']) ?>" data-summary="<?= htmlspecialchars($childConfig['summary']) ?>"
                                    class="block px-5 py-3 text-sm font-bold tracking-wide transition-colors <?= $isChildActive ? 'text-amber-400 bg-slate-900' : 'text-slate-200 hover:bg-slate-900 hover:text-amber-300' ?>">
                                    <?= $childName ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php
                    $isActive = ($currentUrlTrimmed === rtrim($targetUrl, '/'));
                    $desktopClasses = $isActive
                        ? "text-amber-400 hover:text-amber-300 transition-colors focus:outline-none focus:underline"
                        : "text-slate-200 hover:text-amber-300 transition-colors focus:outline-none focus:underline";
                    ?>
                    <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" class="<?= $desktopClasses ?>"><?= $name ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <span class="hidden 2xl:inline text-slate-800 font-bold">|</span>

        <div class="flex items-center gap-2 text-slate-300">
            <?php if ($isLoggedIn && AuthService::isCat()) : ?>
                <button data-reset-button
                    class="hidden md:block group p-2 rounded-xl hover:bg-slate-900 hover:text-secondary-500 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:animate-bounce">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            <?php endif; ?>

            <button id="dark-toggle" title="Toggle Theme"
                class="group p-2 rounded-xl hover:bg-slate-900 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 text-slate-400 block dark:hidden group-hover:scale-125 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <svg class="w-5 h-5 text-secondary-400 hidden dark:block group-hover:scale-125 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>

        <span class="text-slate-800 font-bold">|</span>

        <div class="flex items-center pl-1">
            <?php if ($isLoggedIn): ?>
                <div class="relative group flex items-center gap-2">
                    <a href="<?= $baseUrl ?>logout" data-logout-button title="Sign out"
                        class="flex items-center gap-3 rounded-xl px-3 py-1.5 bg-slate-900 border border-slate-800 hover:border-red-900/60 hover:bg-red-950/40 text-slate-200 hover:text-red-400 transition-all duration-200">
                        <div class="h-6 w-6 rounded-full border border-secondary-400 bg-black flex items-center justify-center text-secondary-400 font-black text-xs shrink-0 group-hover:scale-110 transition-transform shadow-inner">
                            <?= htmlspecialchars($initial ?? 'U') ?>
                        </div>
                        <span class="hidden sm:inline max-w-[160px] truncate text-slate-300 group-hover:text-red-300 transition-colors font-bold"><?= htmlspecialchars($displayName) ?></span>
                        <span class="text-xs uppercase tracking-wider font-black opacity-90 group-hover:opacity-100">Sign Out</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2">
                    <a href="<?= $baseUrl ?>register" data-partial title="Register"
                        class="flex items-center rounded-full px-5 py-2 bg-primary-400 hover:bg-secondary-400 border-2 border-primary-400 hover:border-secondary-400 text-white shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                        <span class="uppercase tracking-widest text-xs font-black">Register</span>
                    </a>
                    <a href="<?= $baseUrl ?>login" data-login-button title="Sign In"
                        class="flex items-center rounded-full px-5 py-2 border-2 border-slate-700 hover:border-primary-400 text-slate-200 hover:text-white transition-all duration-200 transform hover:-translate-y-0.5">
                        <span class="uppercase tracking-widest text-xs font-black">Sign In</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile nav toggle -->
        <div class="flex items-center 2xl:hidden">
            <button type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                aria-label="Toggle Navigation Menu"
                class="text-slate-200 hover:text-amber-300 focus:outline-none p-2 rounded-xl hover:bg-slate-900 transition-colors">
                <svg class="h-6 w-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!mobileMenuOpen">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg class="h-6 w-6 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="mobileMenuOpen" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Backdrop -- dims the page behind the drawer and closes it on click.
         Positioned absolute (not fixed) so it uses the fixed topbar above as
         its containing block and stays pinned without needing to duplicate
         the topbar's own top offset. -->
    <div x-show="mobileMenuOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileMenuOpen = false"
        class="absolute top-full left-0 w-full h-[calc(100vh-76px)] sm:h-[calc(100vh-88px)] 2xl:hidden bg-black/60 z-[9990]">
    </div>

    <!-- Mobile / large-screen nav drawer -- a proper right-anchored sidebar
         panel with a fixed width and its own scroll region, rather than a
         full-bleed dropdown. Body scroll is locked (see x-effect above)
         while it's open, so it never fights the page's own scrollbar. -->
    <div x-show="mobileMenuOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-x-6"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-6"
        class="absolute top-full right-0 2xl:hidden border-l-2 border-slate-800 bg-white dark:bg-black px-4 py-5 space-y-3 shadow-2xl w-full max-w-xs sm:max-w-sm h-[calc(100vh-76px)] sm:h-[calc(100vh-88px)] overflow-y-auto z-[9995]"
        x-data="{ activeMobileSection: null }">

        <?php foreach ($navLinks as $name => $config): ?>
            <?php
            $isHomeItem = (strtolower($name) === 'home' || rtrim($config['url'], '/') === rtrim($baseUrl, '/'));
            $targetUrl = $isHomeItem ? $baseUrl : $config['url'];
            ?>

            <?php if (isset($config['children'])): ?>
                <?php $slug = md5($name); ?>
                <div class="space-y-1.5">
                    <button @click="activeMobileSection = (activeMobileSection === '<?= $slug ?>' ? null : '<?= $slug ?>')"
                        class="w-full flex justify-between items-center px-4 py-3 rounded-xl text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-900 font-bold text-base transition-colors text-left border border-transparent hover:border-slate-200 dark:hover:border-slate-800">
                        <span><?= $name ?></span>
                        <svg class="w-5 h-5 transform transition-transform duration-200 text-slate-500 dark:text-slate-400 stroke-[3]"
                            :class="activeMobileSection === '<?= $slug ?>' ? 'rotate-180 text-primary-600 dark:text-amber-400' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="activeMobileSection === '<?= $slug ?>'" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pl-4 border-l-4 border-slate-300 dark:border-slate-700 space-y-2 ml-4">
                        <?php foreach ($config['children'] as $childName => $childConfig): ?>
                            <?php
                            $isChildActive = ($currentUrlTrimmed === rtrim($childConfig['url'], '/'));
                            $childTargetAttr = isset($childConfig['target']) ? ' target="' . htmlspecialchars($childConfig['target']) . '"' : '';
                            ?>
                            <a href="<?= $childConfig['url'] ?>"<?= $childTargetAttr ?> data-partial data-title="<?= htmlspecialchars($childConfig['title']) ?>" data-summary="<?= htmlspecialchars($childConfig['summary']) ?>" @click="mobileMenuOpen = false"
                                class="block px-4 py-3 rounded-lg text-sm <?= $isChildActive ? 'text-primary-600 dark:text-amber-400 font-black bg-primary-50/50 dark:bg-amber-400/10' : 'text-slate-600 dark:text-slate-300 font-bold hover:text-primary-600 dark:hover:text-amber-400' ?> transition-colors">
                                <?= $childName ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php
                $isActive = ($currentUrlTrimmed === rtrim($targetUrl, '/'));
                $mobileClasses = $isActive
                    ? "block px-4 py-3 rounded-xl bg-primary-50 dark:bg-amber-400/10 text-primary-600 dark:text-amber-400 font-black text-base border-2 border-primary-200 dark:border-amber-400/30"
                    : "block px-4 py-3 rounded-xl text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-900 font-bold text-base transition-colors hover:text-primary-600 dark:hover:text-amber-400 border border-transparent hover:border-slate-200 dark:hover:border-slate-800";
                ?>
                <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" @click="mobileMenuOpen = false" class="<?= $mobileClasses ?>"><?= $name ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($isLoggedIn && $currentUser): ?>
            <?php
            $drawerHasAvatar = !empty($currentUser->avatar_url ?? null);
            $drawerAvatarUrl = $drawerHasAvatar ? $assetBase . 'images/uploads/avatars/' . $currentUser->avatar_url : '';
            $drawerFullName = $currentUser->full_name ?? $displayName;
            ?>
            <div class="pt-5 border-t-2 border-slate-100 dark:border-slate-900 flex items-center gap-4 px-4">
                <div class="h-10 w-10 rounded-full bg-primary-500/10 border-2 border-primary-500 text-primary-600 dark:text-amber-400 flex items-center justify-center font-black text-sm uppercase overflow-hidden shrink-0">
                    <?php if ($drawerHasAvatar): ?>
                        <img src="<?= htmlspecialchars($drawerAvatarUrl) ?>" alt="<?= htmlspecialchars($drawerFullName) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?= htmlspecialchars($initial) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-900 dark:text-slate-100"><?= htmlspecialchars($drawerFullName) ?></p>
                    <p class="text-xs font-bold text-slate-400">Authorized Profile</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
