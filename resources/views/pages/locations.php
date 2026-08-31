<?php
// /resources/views/pages/locations.php

declare(strict_types=1);

/**
 * Guest-facing "Locations" page under the League Details dropdown. Ported
 * from legacy essahockey_live's locations_view.php -- purely static content
 * (address + directions per rink, grouped by sport), no DB table. Not to be
 * confused with the unrelated `locations` table/model already in this app,
 * which is just a short-code lookup for the Schedules form's rink dropdown.
 *
 * Photos are legacy's real venue pics from essahockey_live/images/locations/,
 * copied into public/images/locations/. Legacy itself reused Thornton's
 * outdoor arena photo for the indoor rink too (no distinct indoor photo was
 * ever taken) -- thornton_rink.gif exists in that same legacy folder but was
 * dead code there (never actually wired into a rendered page). Using it here
 * instead of the reuse gives every venue its own distinct photo.
 */

$groups = [
    'Ball Hockey Locations' => [
        [
            'name' => 'Thornton Outdoor Rink',
            'address' => '242 Barrie St, Thornton, ON',
            'image' => 'thornton_arena.png',
        ],
        [
            'name' => 'Angus Outdoor Rink',
            'address' => 'Off the 5th Line, Angus, ON',
            'directions' => '152 Greenwood Drive, Angus (5th Line to Gold Park Gate to Greenwood)',
            'image' => 'angus_rink.png',
        ],
        [
            'name' => 'Alliston Memorial Arena',
            'address' => '49 Nelson St, Alliston, ON',
            'directions' => "From Thornton/Cookstown -- Highway 89 to Church St to Nelson. From Angus/Baxter -- County Rd 10 to Highway 89 to Church St to Nelson.",
            'image' => 'alliston_arena.png',
        ],
    ],
    'Ice Hockey Locations' => [
        [
            'name' => 'Thornton Indoor Rink',
            'address' => '242 Barrie St, Thornton, ON',
            'image' => 'thornton_rink.gif',
        ],
        [
            'name' => 'Innisfil Recreation Centre (YMCA)',
            'address' => '7315 Yonge St, Innisfil, ON L9S 2M6',
            'directions' => 'Home of Summer Ice Hockey.',
            'image' => 'summer_ice.png',
        ],
    ],
];
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php
    $breadcrumbs = ['League Details' => '/league-details', 'Locations' => '/locations'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "Locations" + its NavigationConfig summary for every
    // viewer, admin included (see layout-header.php).
    ?>

    <div class="space-y-14">
        <?php foreach ($groups as $groupTitle => $venues): ?>
            <div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-6 pb-3 border-b border-gray-100 dark:border-gray-800">
                    <?= htmlspecialchars($groupTitle) ?>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($venues as $venue): ?>
                        <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                            <img src="<?= $baseUrl ?>images/locations/<?= htmlspecialchars($venue['image']) ?>" alt="<?= htmlspecialchars($venue['name']) ?>"
                                class="w-full h-40 object-cover" loading="lazy">
                            <div class="p-6">
                                <h3 class="text-sm font-black text-gray-900 dark:text-white"><?= htmlspecialchars($venue['name']) ?></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1"><?= htmlspecialchars($venue['address']) ?></p>
                                <?php if (!empty($venue['directions'])): ?>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mt-2 leading-relaxed"><?= htmlspecialchars($venue['directions']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-14 p-8 rounded-3xl bg-primary-900 dark:bg-secondary-900 text-slate-100 shadow-xl border border-slate-800 relative overflow-hidden">
        <svg class="absolute right-0 bottom-0 opacity-5 w-24 h-24 translate-x-4 translate-y-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Not Sure Where To Go?</p>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-black tracking-tighter">Check Your Season's Schedule</span>
        </div>
        <p class="text-xs mt-2 text-slate-400 leading-relaxed max-w-lg">
            Game-day rinks and venues for your specific division are always listed on that season's schedule.
        </p>
        <a href="<?= $baseUrl ?>schedules" data-partial
            class="inline-flex items-center gap-2 mt-5 px-6 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white font-black text-xs uppercase tracking-widest transition-all active:scale-[0.98]">
            View Schedules
        </a>
    </div>
</div>
