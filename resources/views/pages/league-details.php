<?php
// /resources/views/pages/league-details.php

declare(strict_types=1);

/**
 * Guest-facing "League Details" landing page. Ported from legacy
 * essahockey_live's league_details_view.php -- in legacy this is just two
 * clickable cards ("Ball Hockey" / "Adult Ice Hockey"), each navigating to
 * the same page with a `sub_title` querystring that swaps in a DB-driven
 * list of that sport's leagues/divisions/pricing. Legacy's own card images
 * (images/pix/5.jpg, 6.jpg) don't exist anywhere in that checkout -- broken
 * there today -- so this uses the app's own icon style instead.
 *
 * The two destination pages live at league-details/detail.php, keyed by the
 * "ball-hockey" / "ice-hockey" URL segment (see resolveDynamicPageMeta() in
 * server/helpers.php for how that segment resolves to a title/summary).
 */

$choices = [
    [
        'slug' => 'ball-hockey',
        'title' => 'Ball Hockey',
        'summary' => 'Indoor and outdoor divisions for kids and adults of every age and skill level.',
        'icon' => 'fa-volleyball',
    ],
    [
        'slug' => 'ice-hockey',
        'title' => 'Ice Hockey',
        'summary' => 'Winter and summer leagues across men\'s, women\'s, and 35+ divisions.',
        'icon' => 'fa-hockey-puck',
    ],
];
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php
    $breadcrumbs = ['League Details' => '/league-details'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "League Details" + its NavigationConfig summary for
    // every viewer, admin included (see layout-header.php).
    ?>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400 font-medium mb-10 max-w-xl mx-auto">
        Choose a sport to see its leagues, divisions, and current pricing.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <?php foreach ($choices as $choice): ?>
            <a href="<?= $baseUrl ?>league-details/<?= $choice['slug'] ?>" data-partial
                data-title="<?= htmlspecialchars($choice['title']) ?>" data-summary="<?= htmlspecialchars($choice['summary']) ?>"
                class="group relative flex flex-col items-center text-center p-10 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                data-aos="fade-up" data-aos-duration="700">
                <div class="h-20 w-20 rounded-3xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-5 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid <?= $choice['icon'] ?> text-2xl"></i>
                </div>
                <h3 class="text-lg font-black text-secondary-900 dark:text-white tracking-tight uppercase mb-2"><?= htmlspecialchars($choice['title']) ?></h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed"><?= htmlspecialchars($choice['summary']) ?></p>
                <span class="mt-5 inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-primary-600 dark:text-primary-400">
                    View Details <i class="fa-solid fa-arrow-right text-[9px] group-hover:translate-x-1 transition-transform"></i>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
