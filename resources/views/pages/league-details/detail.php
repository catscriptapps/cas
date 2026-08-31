<?php
// /resources/views/pages/league-details/detail.php

declare(strict_types=1);

/**
 * Per-sport league/division/pricing breakdown for the "League Details"
 * marketing page. Content here is copied VERBATIM off the live legacy site
 * (canadianallstarsports.com's League Details tab) -- the local `essa_hockey`
 * DB checkout this was previously sourced from turned out to be a stale
 * snapshot that no longer matches what's actually live, so the source of
 * truth is the rendered page itself, not that DB.
 *
 * The "Additional Details" blurbs below are legacy's essa_home_page_text
 * entry_id=3 (ball) / entry_id=4 (ice) -- the same admin-editable-content
 * mechanism as this app's own home-page "Our Mission" block (see
 * HomePageTextController). Hardcoded here for now rather than wired into
 * that same WYSIWYG editor; ask if you also want these two made editable
 * from admin the same way.
 *
 * This is intentionally a SEPARATE content source from cas's own
 * `leagues`/`divisions` tables (see server/models/League.php) -- those back
 * the League Management admin module and the live Registration form with
 * cas's current/real season offerings, a different, evolving data set from
 * this legacy marketing page's fixed pricing/leagues.
 */

$content = [
    'ball-hockey' => [
        'leagues' => [
            [
                'name' => 'Thornton Outdoor Arena',
                'divisions' => [
                    ['name' => 'Mens Open Summer Outdoor (Thornton)', 'price' => 190.00],
                    ['name' => 'PUC Summer (Thornton)', 'price' => 115.00],
                    ['name' => 'Adult Co-Ed Fall Outdoor (Thornton)', 'price' => 210.00],
                    ['name' => 'Mens Open Fall Outdoor (Thornton)', 'price' => 210.00],
                    ['name' => 'Womens Fall Outdoor (Thornton)', 'price' => 210.00],
                    ['name' => 'Shack Invitational Fall (Thornton)', 'price' => null],
                ],
            ],
        ],
        'details' => [
            [
                'title' => 'Womens',
                'body' => 'This is a recreational league for Women of all ages. Games will consist of three 10 minute stop time periods and will have a referee. The league is ideal for Women that are new to hockey and wish to "test the waters" but is challenging enough for experienced ball hockey players, and is a good way to meet new friends and have fun. Statistics are tracked on the website. We offer multiple divisions. Competitiveness varies by division/league.',
            ],
            [
                'title' => 'Mens',
                'body' => 'This is a recreational league for Men of all ages. Games will consist of three 10 minute stop time periods and will have two referees. This league is ideal for men looking to stay fit over the course of the spring and summer, and is a good way to meet new friends and build camaraderie. Newcomers are always welcome. We offer Over 35, 3 on 3, Indoor and Mens open (15+) divisions. Statistics are tracked on the website. Competitiveness varies by division/league.',
            ],
            [
                'title' => 'Adult Co-Ed',
                'body' => 'This is a recreational league for both Men and Women of all ages. Games will consist of three 10 minute stop time periods and will have 1-2 referee(s). This league is ideal for people that are new to hockey and wish to "test the waters" but is challenging enough for experienced ball hockey players, and is a good way to meet new friends and have fun. Statistics are tracked on the website.',
            ],
            [
                'title' => 'Kids',
                'body' => '(IP) Instructional Program division: This is an instructional league for children of all ages. The league is open to both boys and girls. Coaches will work with your child, teaching them the basics of ball hockey, along with encouraging them to have fun and make new friends. There will be practice time prior to each game. Games will consist of two 10 minute running periods with stoppages every 2 minutes, along with coaches on the floor. This league is ideal for parents with children that are new to hockey and wish to "test the waters".',
            ],
        ],
    ],
    'ice-hockey' => [
        'leagues' => [
            [
                'name' => 'Winter Ice Hockey (October - April)',
                'divisions' => [
                    ['name' => "Winter Men's Open Ice", 'price' => 465.00],
                    ['name' => "Winter Men's 35+ Ice", 'price' => 465.00],
                    ['name' => "Winter Women's Ice", 'price' => 465.00],
                ],
            ],
        ],
        'details' => [
            [
                'title' => 'Womens Ice Hockey',
                'body' => 'This is a recreational league for Women. Games will consist of three 10 minute stop time periods and will have two referees. This league is ideal for women looking to stay fit over the course of the winter or summer, and is a good way to meet new friends and build camaraderie. Statistics are tracked on the website.',
            ],
            [
                'title' => 'Mens Ice Hockey',
                'body' => "This is a recreational league for Men. Games will consist of three 10 minute stop time periods and will have two referees. This league is ideal for men looking to stay fit over the course of the winter or summer, and is a good way to meet new friends and build camaraderie. Five divisions are offered, 35+, A, B, C and D. Competitiveness varies by division. Statistics are tracked on the website.",
            ],
        ],
    ],
];

$slug = $GLOBALS['encodedId'] ?? '';

if (!isset($content[$slug])) {
    http_response_code(404);
    echo "<div class='p-12 text-center'>
            <h1 class='text-2xl font-black text-gray-900 dark:text-white font-sans uppercase'>League Not Found</h1>
            <a href='{$baseUrl}league-details' data-partial class='text-primary-600 font-bold hover:underline mt-4 inline-block'>Back to League Details</a>
          </div>";
    return;
}

$leagues = $content[$slug]['leagues'];
$details = $content[$slug]['details'];
$breadcrumbLabel = $slug === 'ball-hockey' ? 'Ball Hockey' : 'Ice Hockey';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php
    $breadcrumbs = ['League Details' => '/league-details', $breadcrumbLabel => '/league-details/' . $slug];
    include __DIR__ . '/../../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows the sport name + its NavigationConfig-resolved summary
    // for every viewer, admin included (see layout-header.php).
    ?>

    <div class="space-y-6">
        <?php foreach ($leagues as $league): ?>
            <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm">
                <h2 class="text-base font-black text-secondary-900 dark:text-white uppercase tracking-tight mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                    <?= htmlspecialchars($league['name']) ?>
                </h2>

                <?php if (empty($league['divisions'])): ?>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium italic">Divisions to be announced.</p>
                <?php else: ?>
                    <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                        <?php foreach ($league['divisions'] as $division): ?>
                            <li class="flex items-center justify-between py-2.5 text-sm gap-4">
                                <span class="font-bold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($division['name']) ?></span>
                                <?php if ($division['price'] !== null): ?>
                                    <span class="font-black text-primary-600 dark:text-primary-400 shrink-0">$<?= number_format((float)$division['price'], 2) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="text-[11px] text-red-500 dark:text-red-400 font-bold mt-8 text-center">
        Note: all registration fees are subject to HST.
    </p>

    <?php if (!empty($details)): ?>
        <div class="mt-14">
            <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-6 pb-3 border-b border-gray-100 dark:border-gray-800">
                Additional Details
            </h2>
            <div class="space-y-6">
                <?php foreach ($details as $detail): ?>
                    <div>
                        <h3 class="text-sm font-black text-secondary-900 dark:text-white mb-2"><?= htmlspecialchars($detail['title']) ?></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed"><?= htmlspecialchars($detail['body']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-10 text-center">
        <a href="<?= $baseUrl ?>register" data-partial
            class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-primary-400 hover:bg-secondary-400 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
            Register for <?= htmlspecialchars($breadcrumbLabel) ?>
        </a>
    </div>
</div>
