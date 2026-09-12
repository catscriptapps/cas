<?php
// /resources/views/partials/layout-topbar.php

use Src\Config\NavigationConfig;
use Src\Service\AuthService;

/** @var bool $isLoggedIn */
/** @var bool $isRegistrant */
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

// Path-only, not a full absolute URL -- every $config['url'] this gets
// compared against (below, and in the mobile drawer loop) is itself always
// a bare path like "/schedules", never scheme+host+path. Comparing a full
// "http://host/schedules" against that bare path could never match, which
// silently broke "is this the current nav item" (and therefore the active
// styling below) for every link, on every page, until this was path-only too.
$currentUrlTrimmed = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
?>

<?php
// NOT sticky/fixed -- deliberately plain, normal-flow positioning (kept
// `relative` only so it still works as the containing block for the
// mobile drawer/backdrop's `absolute` positioning below). It lives inside
// layout-header.php's shared Alpine component (mobileMenuOpen/isHome/
// isNoHeroPage/etc are all declared on that outer wrapper, not here) so its
// background can react to whether a hero is actually behind it right now.
//
// The negative margin-bottom is what makes it OVERLAY the hero instead of
// pushing it down: this element still reserves its own ~72px in normal
// document flow (so a no-hero page's content starts right after it,
// completely normally -- solid/no-hero pages get their own taller
// logo-driven min-height instead, since the logo is no longer part of this
// row at all, see below), but when a hero IS present, pulling the next
// sibling up by that same amount makes the hero's own background start at
// y=0 while this stays visually on top of its first ~72px. That value has
// to match this element's real rendered height exactly, or a gap (too
// little) or overlap (too much) appears -- see the -mt-1.5/pt-104px trail
// of comments this replaced for how finicky that used to be to keep in
// sync across two separate elements; here it's one element referencing its
// own height, so there's only one number to keep correct. It no longer
// needs an isHome-specific pair of values either (like the old
// 126/142-vs-190/222px split did) -- now that the logo is sized/positioned
// completely independently (see the logo comment below), this row's own
// height is driven only by the nav text and icon cluster, which don't
// change between home and inner pages.
?>
<?php
// pt-[16px]/pb-[16px] below is deliberately fixed px, NOT Tailwind's
// rem-based pt-4/etc scale. This topbar's rendered height has to stay
// pinned to an exact px value (see the min-h/-mb comment above) -- if it
// were rem-based, a bigger root font-size (some Smart TV browsers default
// to ~24px instead of 16px for 10-foot viewing, and so does any OS/browser
// "larger text" accessibility setting) would inflate the padding well past
// what the hardcoded min-h/-mb sync values expect, without touching those
// px sync values at all -- the topbar would render taller than its
// reserved space, spilling its own extra height down over the hero's
// title/badge (which sits at the top of the hero, i.e. exactly where that
// spillover lands). Fixed px keeps the topbar's real height constant
// regardless of root font-size, which is what actually fixes that.
?>
<div class="relative w-full text-slate-200 px-4 sm:px-6 lg:px-8 pt-[16px] pb-[16px] text-sm sm:text-base flex justify-between items-center transition-colors duration-300 select-none z-[9999]"
    :class="{
        'min-h-[72px] -mb-[72px]': !isNoHeroPage && !isAboutPage && !isDetailPage,
        'min-h-[120px] sm:min-h-[136px]': isNoHeroPage || isAboutPage || isDetailPage,
        'bg-black border-b-2 border-gray-700 shadow-xl': isNoHeroPage || isAboutPage || isDetailPage,
        <?php
        // No border at all here (not even a transparent one) -- a
        // transparent border is still invisible, but its border-width still
        // adds to this element's rendered height same as a visible one
        // would, which was quietly 2px taller than the -mb-[72px] pulling
        // the hero up to compensate for it, leaving the hero's own
        // background starting 2px below the actual viewport top instead of
        // flush with it (a thin sliver of the page's own white background
        // showing through in the gap, since the topbar itself has nothing
        // opaque painted there to hide it).
        ?>
        'bg-transparent': !(isNoHeroPage || isAboutPage || isDetailPage),
    }"
    x-effect="document.body.style.overflow = mobileMenuOpen ? 'hidden' : ''">

    <?php
    // This spacer is a plain, near-invisible flex item that exists ONLY to
    // reserve the logo's own WIDTH in the row's flex layout (so the
    // centered-nav wrapper next to it still centers in the right place, and
    // the icon cluster on the far right doesn't shift) -- it does NOT
    // reserve the logo's HEIGHT, since it has none of its own. The actual
    // logo (below, absolutely positioned) is now completely decoupled from
    // this row's height: previously the logo was a normal flex child, so a
    // bigger logo directly made the whole row (and therefore the hardcoded
    // min-h/-mb sync above) taller, which pushed the nav row down away from
    // the top of the viewport and forced a much bigger sync value that had
    // to differ between home and inner pages. Taking it out of flow means
    // this row can stay short and sit close to the top regardless of logo
    // size, and the logo can render at whatever size looks right without
    // dragging the row (and therefore the hero underneath it) along with
    // it -- it simply overlaps down onto the hero image below, which reads
    // fine since it's off to the left while the hero's own title/badge is
    // centered.
    ?>
    <?php
    // hidden below xl: the nav-wrapper it exists to make room for is itself
    // `hidden xl:flex` (see below), so below xl there's no nav centering
    // math for it to protect -- reserving its width there anyway only
    // shoved the icon cluster (and the hamburger button inside it) off the
    // right edge of narrow/mobile viewports, since it was competing with a
    // logo-sized gap that has no visible nav next to it to justify.
    ?>
    <?php
    // xl is always >= the sm breakpoint, so the logo here is always
    // rendering at its sm+ size (176px/104px) by the time this spacer is
    // even visible -- matching that (not the smaller sub-sm size) is what
    // keeps the nav actually centered against the real logo width.
    ?>
    <div class="hidden xl:block shrink-0" :class="isHome ? 'xl:w-[176px]' : 'xl:w-[104px]'"></div>

    <?php
    // The actual logo -- absolutely positioned against this row's own
    // `relative` container (not a flex item, see the spacer comment above),
    // so its height plays no part in this row's own height. left-[Npx]
    // matches the row's own px-4/sm:px-6/lg:px-8 padding in fixed-px form
    // (for the same root-font-size-independence reason as pt/pb above), and
    // top-[Npx] is the "push it down a little from the very top of the
    // viewport" offset -- both are independent numbers now, free to tune
    // without needing to keep some OTHER element's height in sync.
    ?>
    <div class="absolute left-[16px] sm:left-[24px] lg:left-[32px] top-[12px] z-10">
        <a href="<?= $baseUrl ?>" data-partial data-title="Home" class="flex items-center gap-3 min-w-0 transition-opacity hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-amber-500 rounded-lg">
            <?php
            // Legacy's own header has no "Canadian All Star Sports" text next
            // to the logo -- the crest graphic itself already spells out the
            // name -- so this doesn't repeat it either. alt text keeps the
            // name available to screen readers regardless.
            ?>
            <img src="<?= $assetBase ?>images/logo/logo.png" alt="<?= htmlspecialchars($appName) ?>" class="object-contain shrink-0"
                :class="isHome ? 'h-[144px] w-[144px] sm:h-[176px] sm:w-[176px]' : 'h-[88px] w-[88px] sm:h-[104px] sm:w-[104px]'">
        </a>
    </div>

    <!-- Nav items, centered in the remaining space between the logo and the
         icon cluster (this flex-1 wrapper is what centers it -- the logo and
         icon-cluster divs stay shrink-0 on either side). -->
    <?php
    // xl (1280px), not lg (1024px) -- this staff nav has a lot of items
    // (Home, Schedules, Stats+Standings, League Details ▾, Sponsorship,
    // Contact, plus the reset icon for the "cat" role), and between
    // 1024-1279px there just isn't enough room for that plus the logo and
    // icon cluster on one line even after shrinking the nav's own font/gaps
    // and trimming the account pill down to just its avatar (see those
    // other comments in this file) -- it would still spill out of this
    // flex-1 slot and visually collide with the icon cluster next to it
    // (exactly what a "1280x720" resolution -- landscape iPad/tablet width,
    // and a common "big screen TV" one too -- hit). Below xl, the hamburger
    // menu (in the icon cluster, see lg:hidden below -- unchanged wording
    // there since it's a smaller, separate concern) is the reliable
    // fallback instead of trying to cram the full row in.
    ?>
    <div class="hidden xl:flex flex-1 justify-center min-w-0">
        <?php
        // whitespace-nowrap is load-bearing, not cosmetic: without it, a
        // multi-word item ("League Details") can wrap onto a second line
        // the moment this row is even slightly tight for space (e.g. a
        // 1280x720 "big screen TV" resolution, which has plenty of width on
        // paper but not enough for logo + full nav + icon cluster on one
        // line at the old font size). A wrapped item makes the topbar's own
        // content taller than its hardcoded min-h/-mb sync value (see that
        // comment above), which spills the extra height down over the hero
        // title/badge sitting right below -- forcing this to a single line
        // instead means the row just tightens its gaps / this wrapper
        // scrolls-if-needed, but the topbar's height itself never changes.
        ?>
        <nav class="flex items-center gap-3 xl:gap-5 text-[16px] font-black uppercase tracking-wide text-slate-200 whitespace-nowrap">
            <?php foreach ($navLinks as $name => $config): ?>
                <?php
                // Detect if this element represents the Home link
                $isHomeItem = (strtolower($name) === 'home' || rtrim($config['url'], '/') === rtrim($baseUrl, '/'));
                $targetUrl = $isHomeItem ? $baseUrl : $config['url'];
                ?>

                <?php if (isset($config['children'])): ?>
                    <div class="relative group flex items-center h-full cursor-pointer py-2">
                        <?php
                        // The reveal-underline effect matches legacy's own
                        // navbar exactly (essahockey.com's `.navbar > ul >
                        // li > a::before` rule): a 3px bar pinned to the
                        // link's left edge that grows to full width on
                        // hover/focus and stays fixed full-width when the
                        // link is the current page -- since `left` never
                        // moves, growing reads as sliding in left-to-right,
                        // and shrinking back on hover-out reads as the
                        // opposite (its only moving edge retreats
                        // rightward-to-leftward toward the fixed left anchor).
                        $isActive = ($currentUrlTrimmed === rtrim($targetUrl, '/'));
                        $underline = "relative before:content-[''] before:absolute before:left-0 before:-bottom-1.5 before:h-[3px] before:bg-white before:transition-[width] before:duration-300 before:ease-in-out";
                        $desktopClasses = $isActive
                            ? "text-amber-400 group-hover:text-amber-300 transition-colors flex items-center gap-1.5 focus:outline-none {$underline} before:w-full"
                            : "text-slate-200 hover:text-amber-300 transition-colors flex items-center gap-1.5 focus:outline-none {$underline} before:w-0 group-hover:before:w-full focus:before:w-full";
                        ?>
                        <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" class="<?= $desktopClasses ?>">
                            <span><?= $name ?></span>
                            <svg class="w-3.5 h-3.5 transform group-hover:rotate-180 transition-transform duration-200 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>

                        <div class="absolute top-full left-0 min-w-[240px] bg-slate-950 dark:bg-black border-2 border-slate-800 dark:border-slate-900 rounded-xl shadow-2xl py-3 opacity-0 scale-95 pointer-events-none group-hover:opacity-100 group-hover:scale-100 group-hover:pointer-events-auto transition-all duration-150 z-50">
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
                    $underline = "relative before:content-[''] before:absolute before:left-0 before:-bottom-1.5 before:h-[3px] before:bg-white before:transition-[width] before:duration-300 before:ease-in-out";
                    $desktopClasses = $isActive
                        ? "text-amber-400 hover:text-amber-300 transition-colors focus:outline-none {$underline} before:w-full"
                        : "text-slate-200 hover:text-amber-300 transition-colors focus:outline-none {$underline} before:w-0 hover:before:w-full focus:before:w-full";
                    ?>
                    <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" class="<?= $desktopClasses ?>"><?= $name ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Icon cluster + auth + mobile toggle, far right. Stays shrink-0 so
         the flex-1 nav wrapper above absorbs all the remaining space between
         it and the logo, which is what actually centers the nav. -->
    <?php
    // ml-auto, not just justify-between on the row -- below xl the nav
    // wrapper AND the logo-width spacer are both `hidden`, leaving this as
    // the row's only flex item, and `justify-between` has nothing left to
    // put space "between", so it collapsed to the left (flex-start) instead
    // of staying pinned to the right. ml-auto pushes it right unconditionally,
    // regardless of how many siblings happen to be visible at a given width.
    ?>
    <div class="flex items-center gap-3 sm:gap-5 font-bold shrink-0 ml-auto">

        <div class="flex items-center gap-2 text-slate-300">
            <?php if ($isLoggedIn && AuthService::isCat()) : ?>
                <button data-reset-button
                    class="hidden md:block group p-2 rounded-xl hover:bg-slate-900 hover:text-secondary-500 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:animate-bounce">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            <?php endif; ?>

            <?php if ($isLoggedIn || $isRegistrant): ?>
                <?php
                // Dashboard is reached via this icon now, not a top nav
                // item (see NavigationConfig::getNavLinks()'s docblock) --
                // kept visually louder than the plain icon buttons around it
                // (colored pill, not just a hover state) since it's the one
                // real "go to my workspace" action up here.
                $isAdminViewer = $isLoggedIn && AuthService::isAdmin();
                $dashboardUrl = $isAdminViewer ? $baseUrl . 'dashboard' : $baseUrl . 'my-account';
                $dashboardTitle = $isAdminViewer ? 'Operational Dashboard' : 'Dashboard';
                $dashboardSummary = $isAdminViewer
                    ? 'Recent activity across the league, and quick links into every workspace module.'
                    : 'Your registration status, team, schedule, and stats, all in one place.';
                ?>
                <a href="<?= $dashboardUrl ?>" data-partial data-title="<?= htmlspecialchars($dashboardTitle) ?>" data-summary="<?= htmlspecialchars($dashboardSummary) ?>" title="Dashboard" aria-label="Dashboard"
                    class="flex items-center justify-center h-10 w-10 rounded-xl bg-primary-500/15 hover:bg-primary-500/25 text-primary-400 hover:text-primary-300 border border-primary-500/30 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </a>

                <?php if ($isAdminViewer): ?>
                    <?php
                    // Admin-only (matches AuthService::isAdmin(), stricter than the
                    // general isLoggedIn staff guard above) -- the unread badge count
                    // is seeded server-side into the shared Alpine scope
                    // (unreadMessagesCount, see layout-header.php) and kept live
                    // afterward via a 'messages-unread-changed' window event, since
                    // this element never re-renders on its own across an SPA
                    // navigation (see resources/js/utils/messages/unread-badge.js
                    // and its call sites for every place that fires it).
                    ?>
                    <a href="<?= $baseUrl ?>messages" data-partial data-title="Messages" data-summary="Submissions from the public Contact Us form -- read, archive, and manage." title="Messages" aria-label="Messages"
                        class="relative flex items-center justify-center h-10 w-10 rounded-xl bg-primary-500/15 hover:bg-primary-500/25 text-primary-400 hover:text-primary-300 border border-primary-500/30 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span x-show="unreadMessagesCount > 0" x-cloak x-text="unreadMessagesCount > 99 ? '99+' : unreadMessagesCount"
                            class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-black leading-none border-2 border-slate-950 dark:border-black"></span>
                    </a>
                <?php endif; ?>
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
                        <?php
                        // Both labels wait until min-[1400px] (well past `lg`/`xl`,
                        // where the desktop nav itself first turns on) rather than
                        // the usual sm/lg scale -- staff nav has a lot of items
                        // (Home, Schedules, Stats+Standings, League Details ▾,
                        // Sponsorship, Contact, plus the reset icon for the "cat"
                        // role), and this pill was the widest thing in the icon
                        // cluster competing with it for room. At exactly the width
                        // a common "big screen" resolution like 1280x720 reports,
                        // showing the full name + "Sign Out" text left too little
                        // space for that full nav to fit on one line, so it spilled
                        // outside its own flex-1 slot and visually collided with
                        // this pill. Below 1400px this now just shows the avatar.
                        ?>
                        <span class="hidden min-[1400px]:inline max-w-[160px] truncate text-slate-300 group-hover:text-red-300 transition-colors font-bold"><?= htmlspecialchars($displayName) ?></span>
                        <span class="hidden min-[1400px]:inline text-xs uppercase tracking-wider font-black opacity-90 group-hover:opacity-100">Sign Out</span>
                    </a>
                </div>
            <?php elseif ($isRegistrant): ?>
                <?php
                // No separate Dashboard icon here anymore -- "Dashboard" is
                // now a plain nav item right after Home for every signed-in
                // account (see NavigationConfig::getNavLinks()), so this
                // cluster only needs Sign Out, matching the staff pattern above.
                ?>
                <div class="flex items-center gap-2">
                    <a href="<?= $baseUrl ?>logout" data-logout-button title="Sign out" aria-label="Sign out"
                        class="flex items-center justify-center h-10 w-10 rounded-full border-2 border-slate-700 hover:border-red-900/60 text-slate-200 hover:text-red-400 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9V5.25A2.25 2.25 0 0015 3H6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 006 21h9a2.25 2.25 0 002.25-2.25V15M21 12H9m12 0l-3-3m3 3l-3 3" />
                        </svg>
                    </a>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2">
                    <a href="<?= $baseUrl ?>register" data-partial title="Register"
                        class="flex items-center rounded-full px-5 py-2 bg-primary-400 hover:bg-secondary-400 border-2 border-primary-400 hover:border-secondary-400 text-white shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                        <span class="uppercase tracking-widest text-xs font-black">Register</span>
                    </a>
                    <a href="<?= $baseUrl ?>login" data-login-button title="Sign In" aria-label="Sign In"
                        class="flex items-center justify-center h-10 w-10 rounded-full border-2 border-slate-700 hover:border-primary-400 text-slate-200 hover:text-white transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile nav toggle -- xl:hidden to match the xl:flex desktop nav
             above; below xl this hamburger is the only way to reach the nav. -->
        <div class="flex items-center xl:hidden">
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
        class="absolute top-full left-0 w-full xl:hidden bg-black/60 z-[9990]"
        :class="isHome ? 'h-[calc(100vh-190px)] sm:h-[calc(100vh-222px)]' : 'h-[calc(100vh-126px)] sm:h-[calc(100vh-142px)]'">
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
        class="absolute top-full right-0 xl:hidden border-l-2 border-slate-800 bg-white dark:bg-black px-4 py-5 space-y-3 shadow-2xl w-full max-w-xs sm:max-w-sm overflow-y-auto z-[9995]"
        :class="isHome ? 'h-[calc(100vh-190px)] sm:h-[calc(100vh-222px)]' : 'h-[calc(100vh-126px)] sm:h-[calc(100vh-142px)]'"
        x-data="{ activeMobileSection: null }">

        <?php foreach ($navLinks as $name => $config): ?>
            <?php
            $isHomeItem = (strtolower($name) === 'home' || rtrim($config['url'], '/') === rtrim($baseUrl, '/'));
            $targetUrl = $isHomeItem ? $baseUrl : $config['url'];
            ?>

            <?php if (isset($config['children'])): ?>
                <?php $slug = md5($name); ?>
                <div class="space-y-1.5">
                    <div class="flex items-center rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors">
                        <!-- Tapping the name navigates straight to the landing page (e.g.
                             /league-details) -- on a screen stuck behind this hamburger
                             (no hover dropdown available), that page was otherwise
                             completely unreachable, since the old combined button only
                             ever toggled the submenu open/closed and never itself linked
                             anywhere. -->
                        <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" @click="mobileMenuOpen = false"
                            class="flex-1 min-w-0 px-4 py-3 text-slate-800 dark:text-slate-100 font-bold text-base">
                            <?= $name ?>
                        </a>
                        <button type="button" @click="activeMobileSection = (activeMobileSection === '<?= $slug ?>' ? null : '<?= $slug ?>')"
                            aria-label="Toggle <?= htmlspecialchars($name) ?> submenu"
                            class="shrink-0 p-3 pl-2 text-slate-500 dark:text-slate-400">
                            <svg class="w-5 h-5 transform transition-transform duration-200 stroke-[3]"
                                :class="activeMobileSection === '<?= $slug ?>' ? 'rotate-180 text-primary-600 dark:text-amber-400' : ''"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

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
        <?php elseif ($isRegistrant): ?>
            <?php
            // No separate Dashboard link needed here -- the topbar's
            // Dashboard icon (next to the dark-mode toggle) is in the
            // "always visible" icon cluster, not the lg:hidden nav/hamburger
            // pair, so it's already reachable on mobile without opening this
            // drawer at all. This block just shows who's signed in, matching
            // the staff pattern above, plus Sign Out.
            ?>
            <div class="pt-5 border-t-2 border-slate-100 dark:border-slate-900 space-y-3">
                <div class="flex items-center gap-4 px-4 py-2">
                    <div class="h-10 w-10 rounded-full bg-primary-500/10 border-2 border-primary-500 text-primary-600 dark:text-amber-400 flex items-center justify-center font-black text-sm uppercase shrink-0">
                        <?= htmlspecialchars($initial ?? 'R') ?>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-900 dark:text-slate-100"><?= htmlspecialchars($displayName) ?></p>
                        <p class="text-xs font-bold text-slate-400">Registrant Account</p>
                    </div>
                </div>
                <a href="<?= $baseUrl ?>logout" data-logout-button @click="mobileMenuOpen = false"
                    class="block px-4 py-3 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 font-bold text-base transition-colors">
                    Sign Out
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
