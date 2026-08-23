<?php
// /resources/views/partials/layout-header.php

declare(strict_types=1);

use Src\Config\NavigationConfig;

/** @var bool $isLoggedIn */
/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */

// Hero background rotation -- a fixed set of stock images rather than a
// database-managed slideshow (there's no admin module for this yet).
$slideshowImages = ['hero-1.png', 'hero-2.png', 'hero-4.png', 'hero-7.png', 'hero-9.png', 'hero-11.png', 'hero-13.png', 'hero-17.png'];

$totalSlides = count($slideshowImages);

// Detect initial load context for server rendering state.
// Normalized the same way as public/index.php's routing so '/', '/index.php',
// and the canonical '/home' (however it's reached in prod vs local) all agree.
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$currentBasePath = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
$initialIsHome = (normalizePath($currentPath, $currentBasePath) === '/home' || $currentPath === '/index.php');

// --- Universal Title & Summary Injection for Hard Refreshes ---
// Role-based fallback defaults, used only if the current path isn't found
// in anyone's nav-link set below (e.g. a role landing on a page outside
// their own workspace).
if ($isLoggedIn) {
    $initialPageTitle = 'Operational Dashboard';
    $initialPageSummary = 'Recent activity across the league, and quick links into every workspace module.';
} else {
    $initialPageTitle = '';
    $initialPageSummary = '';
}

// Match the current path against whichever nav-link set applies to the
// signed-in entity (same lookup the SPA partial-load X-Page-Title/
// X-Page-Summary headers use — see resolvePageRoute() in helpers.php —
// so a page's summary doesn't depend on how you navigated to it).
$matchedMeta = NavigationConfig::resolveMetaForPath($currentPath);
if ($matchedMeta) {
    $initialPageTitle = $matchedMeta['title'];
    $initialPageSummary = $matchedMeta['summary'];
}

// Dynamic detail-route pages (e.g., the token-based tenant portal) can't be
// matched by NavigationConfig above, since their URL differs per instance.
// resolvePageRoute() stashes a summary (and resolves $title dynamically)
// for these before this file is included — respect it here if present.
if (!empty($GLOBALS['pageSummary'])) {
    $initialPageTitle = $title ?? $initialPageTitle;
    $initialPageSummary = $GLOBALS['pageSummary'];
}
// The hero/slideshow is guest-only real estate -- once signed in, every
// reachable page (Dashboard, Slideshow, Profile, Users) uses that vertical
// space for its own content instead. Gating this server-side (rather than
// leaving it to the Alpine isLoggedIn flag below) means a logged-in render
// never ships the slideshow markup/images at all.
if ($isLoggedIn) {
    return;
}
?>

<div class="w-full relative bg-gray-900 dark:bg-black transition-all duration-500 font-sans"
    :class="(isHome && !isLoggedIn) ? 'min-h-[min(420px,55vh)]' : 'min-h-[min(220px,30vh)] sm:min-h-[min(240px,30vh)]'"
    x-data="{ 
        activeSlide: 1,
        slidesCount: <?= $totalSlides ?>,
        mobileMenuOpen: false,
        isHome: <?= $initialIsHome ? 'true' : 'false' ?>,
        isLoggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>,
        pageTitle: '<?= addslashes($initialPageTitle) ?>',
        pageSummary: '<?= addslashes($initialPageSummary) ?>',
        init() {
            setInterval(() => {
                this.activeSlide = this.activeSlide === this.slidesCount ? 1 : this.activeSlide + 1;
            }, 8000);
        }
    }"
    @spa-navigation.window="
        isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>; // Re-evaluate on navigation
        isHome = $event.detail.isHome;
        pageTitle = $event.detail.title || '';
        pageSummary = $event.detail.summary || '';
    ">

    <div class="absolute inset-0 z-0 overflow-hidden">
        <?php foreach ($slideshowImages as $index => $imageName): ?>
            <?php $slideNumber = $index + 1; ?>
            <div
                x-show="activeSlide === <?= $slideNumber ?>"
                <?php if ($slideNumber > 1): ?>x-cloak<?php endif; ?>
                x-transition:enter="transition ease-in-out duration-1000"
                class="absolute inset-0 bg-cover bg-center animate-slideshow-zoom"
                style="background-image: url('<?= $assetBase ?>images/home/<?= $imageName ?>');">
            </div>
        <?php endforeach; ?>

        <!-- Light overall wash -- brand-tinted navy instead of flat black, keeps the slideshow feeling airy -->
        <div class="absolute inset-0 bg-gradient-to-b from-white/10 via-transparent to-primary-950/25 dark:from-primary-950/30 dark:via-primary-950/10 dark:to-primary-950/50"></div>

        <!-- Targeted band behind the nav row (and the compact page-title bar on inner pages) so text stays readable regardless of slide content -->
        <div class="absolute inset-x-0 top-0 h-40 sm:h-48 bg-gradient-to-b from-primary-950/50 via-primary-950/20 to-transparent"></div>

        <!-- Soft vignette centered on the hero copy so it stays legible without darkening the whole image -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(2,15,44,0.35)_0%,transparent_62%)]"></div>

        <!-- Warm accent glow -- a touch of brand color instead of a purely neutral overlay -->
        <div class="absolute -bottom-16 right-0 w-96 h-96 bg-secondary-500/20 rounded-full blur-[110px] pointer-events-none"></div>
    </div>

    <div class="relative z-10 w-full flex flex-col flex-1">

        <section x-show="isHome && !isLoggedIn" x-collapse.duration.500ms class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-12 sm:pb-16">
            <div class="max-w-4xl mx-auto text-center">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-widest bg-primary-500/20 text-primary-300 border border-primary-500/30 backdrop-blur-sm mb-4 drop-shadow-sm"
                    data-aos="fade-down"
                    data-aos-duration="600">
                    <i class="fa-solid fa-hockey-puck text-[10px] text-primary-400"></i> Empowering Communities Through Hockey Since 2008
                </span>

                <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight uppercase drop-shadow-[0_4px_12px_rgba(0,0,0,0.6)] mb-3"
                    data-aos="fade-up"
                    data-aos-duration="800"
                    data-aos-delay="100">
                    Canadian All Star Sports
                </h1>

                <p class="max-w-2xl mx-auto text-sm sm:text-base text-slate-100 drop-shadow-[0_2px_6px_rgba(0,0,0,0.55)] font-medium mb-6"
                    data-aos="fade-up"
                    data-aos-duration="800"
                    data-aos-delay="200">
                    Real-time schedules, stats, and standings for every league we run -- built for players, referees, and the communities behind them.
                </p>

                <a href="<?= $baseUrl ?>contact" data-partial
                    data-aos="fade-up" data-aos-duration="800" data-aos-delay="300"
                    class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-secondary-500 hover:bg-secondary-400 text-slate-900 font-black text-xs uppercase tracking-widest shadow-lg shadow-secondary-500/30 transition-all duration-300 active:scale-[0.98]">
                    Get In Touch
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </section>

        <section x-show="!isHome || isLoggedIn" x-cloak class="flex-1 flex items-center px-4 sm:px-6 lg:px-8 py-3 sm:py-5">
            <div class="max-w-7xl mx-auto w-full">
                <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight uppercase mb-1 drop-shadow-[0_3px_8px_rgba(0,0,0,0.6)]" x-text="pageTitle"></h1>
                <p class="text-xs text-slate-200/90 max-w-2xl font-normal drop-shadow-[0_2px_5px_rgba(0,0,0,0.55)] leading-normal" x-text="pageSummary"></p>
            </div>
        </section>

    </div>
</div>