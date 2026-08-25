<?php
// /resources/views/pages/home.php

declare(strict_types=1);

/** @var string $baseUrl  */
?>

<section class="relative overflow-hidden py-20 px-6 sm:px-12 lg:px-24 xl:px-32">
    <div class="absolute -top-24 -left-24 w-[400px] h-[400px] bg-primary-500/[0.06] dark:bg-primary-500/[0.08] rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-[400px] h-[400px] bg-secondary-500/[0.06] dark:bg-secondary-500/[0.08] rounded-full blur-[130px] pointer-events-none"></div>

    <div class="max-w-3xl mx-auto relative z-10 text-center space-y-4" data-aos="fade-up" data-aos-duration="700">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
            <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                Who We Are
            </p>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
            Empowering Communities Through Hockey Since 2008
        </h2>
        <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 font-medium leading-relaxed max-w-2xl mx-auto">
            Canadian All Star Sports runs the leagues, seasons, and game days that keep local players, referees, and rinks
            connected -- schedules, live stats and standings, and everything in between, all in one place.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a href="<?= $baseUrl ?>register?sport_id=1" data-partial
                class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-primary-400 hover:bg-secondary-400 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
                Register for Ice Hockey
            </a>
            <a href="<?= $baseUrl ?>register?sport_id=2" data-partial
                class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-secondary-400 hover:bg-primary-400 text-slate-900 hover:text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-secondary-500/20 transition-all active:scale-[0.98]">
                Register for Ball Hockey
            </a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto mt-16 grid grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
        <?php
        $quickLinks = [
            ['icon' => 'fa-calendar-days', 'title' => 'Schedules', 'summary' => 'Game days, times, and rinks for every division.', 'url' => $baseUrl . 'schedules'],
            ['icon' => 'fa-chart-simple', 'title' => 'Stats+Standings', 'summary' => 'Live team standings and player leaderboards.', 'url' => $baseUrl . 'stats'],
            ['icon' => 'fa-people-group', 'title' => 'League Details', 'summary' => 'Locations, rules, and required equipment.', 'url' => $baseUrl . 'locations'],
            ['icon' => 'fa-envelope', 'title' => 'Contact Us', 'summary' => 'Questions about a league or getting involved.', 'url' => $baseUrl . 'contact'],
        ];
        ?>
        <?php foreach ($quickLinks as $i => $link): ?>
            <?php $tag = isset($link['url']) ? 'a' : 'div'; ?>
            <<?= $tag ?> <?= isset($link['url']) ? 'href="' . htmlspecialchars($link['url']) . '" data-partial' : '' ?>
                class="group relative flex flex-col items-center text-center p-6 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm <?= isset($link['url']) ? 'hover:shadow-xl hover:-translate-y-1 cursor-pointer' : '' ?> transition-all duration-300"
                data-aos="fade-up" data-aos-duration="700" data-aos-delay="<?= $i * 100 ?>">
                <div class="h-14 w-14 rounded-2xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-4 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid <?= $link['icon'] ?> text-lg"></i>
                </div>
                <h3 class="text-sm font-black text-secondary-900 dark:text-white tracking-tight mb-1.5 uppercase"><?= htmlspecialchars($link['title']) ?></h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed"><?= htmlspecialchars($link['summary']) ?></p>
                <?php if (!isset($link['url'])): ?>
                    <span class="mt-3 inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-secondary-100 dark:bg-secondary-900/30 text-secondary-700 dark:text-secondary-400">Coming Soon</span>
                <?php endif; ?>
            </<?= $tag ?>>
        <?php endforeach; ?>
    </div>
</section>

<section class="relative overflow-hidden bg-slate-50 dark:bg-slate-950 py-20 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-y border-slate-200 dark:border-slate-800 left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="max-w-2xl mx-auto relative z-10 text-center space-y-4 mb-14" data-aos="fade-up" data-aos-duration="700">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-secondary-500 dark:bg-secondary-400 animate-pulse"></span>
            <p class="uppercase tracking-[0.25em] text-[10px] font-black text-secondary-600 dark:text-secondary-400">
                What We Run
            </p>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            Built For <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-600 dark:from-primary-400 dark:to-secondary-400">Every Division</span>
        </h2>
        <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 font-medium leading-relaxed max-w-xl mx-auto">
            One platform behind every season we run, from the first puck drop to the final standings.
        </p>
    </div>

    <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
        <?php
        $features = [
            [
                'icon'    => 'fa-calendar-check',
                'title'   => 'Game Scheduling',
                'summary' => 'Divisions, rinks, referees, and timekeepers coordinated across every season.',
            ],
            [
                'icon'    => 'fa-ranking-star',
                'title'   => 'Live Stats & Standings',
                'summary' => 'Wins, losses, goals, and player leaderboards updated as gamesheets come in.',
            ],
            [
                'icon'    => 'fa-user-group',
                'title'   => 'Team & Player Rosters',
                'summary' => 'Registrations, teams, and rosters kept organized from tryouts to playoffs.',
            ],
            [
                'icon'    => 'fa-shield-heart',
                'title'   => 'On-Ice Safety',
                'summary' => 'Incident reporting and league contacts, so every rink stays accountable.',
            ],
        ];
        ?>
        <?php foreach ($features as $i => $feature): ?>
            <div class="group relative p-7 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                data-aos="fade-up" data-aos-duration="700" data-aos-delay="<?= $i * 100 ?>">
                <div class="h-12 w-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-5 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid <?= $feature['icon'] ?> text-lg"></i>
                </div>
                <h3 class="text-base font-black text-secondary-900 dark:text-white tracking-tight mb-2"><?= htmlspecialchars($feature['title']) ?></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed"><?= htmlspecialchars($feature['summary']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="faqs" class="relative py-20 px-6 sm:px-12 lg:px-24 xl:px-32">
    <div class="max-w-2xl mx-auto relative z-10 text-center space-y-4 mb-12" data-aos="fade-up" data-aos-duration="700">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
            <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                FAQ
            </p>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            Frequently Asked Questions
        </h2>
    </div>

    <div class="max-w-3xl mx-auto space-y-3 relative z-10" x-data="{ open: 0 }">
        <?php
        // 'a' is trusted, developer-authored HTML (not user input) -- some
        // answers need a list or a link, so it's rendered raw below rather
        // than htmlspecialchars()'d.
        $faqs = [
            ['q' => 'When does the season start?', 'a' => 'Season start dates vary by league and division -- check the <a href="' . $baseUrl . 'schedules" data-partial class="text-primary-600 font-bold hover:underline">Schedules</a> page for exact dates for your division.'],
            ['q' => 'What equipment do I need?', 'a' => '<ul class="list-disc pl-5 space-y-1.5">
                <li>Indoor Ball Hockey requires a helmet, gloves, running shoes, and a stick (jock/jill and shin guards are highly recommended)</li>
                <li>Outdoor Ball Hockey requires gloves, running shoes, and a stick (helmet, jock/jill, and shin guards are highly recommended)</li>
                <li>Ice Hockey requires a helmet, gloves, jock/jill, chest protector, shin guards, hockey pants, and a stick (a facial visor or cage is highly recommended)</li>
            </ul>
            <p class="mt-3">Full breakdowns are on the <a href="' . $baseUrl . 'equipment-required" data-partial class="text-primary-600 font-bold hover:underline">Required Equipment</a> page.</p>'],
            ['q' => 'What is the cost?', 'a' => '<ul class="list-disc pl-5 space-y-1.5">
                <li>Adult Ball Hockey leagues: $210 + HST per person for the season (or a team-fee option)</li>
                <li>Outdoor team fee: $2,400 for Women\'s and Coed 4-on-4 (11-player minimum roster); $2,200 for Men\'s 3-on-3 (10-player minimum roster)</li>
                <li>Indoor team fee: $2,800 for Women\'s, Men\'s, and Coed (14-player minimum roster)</li>
                <li>Ice Hockey Spring/Summer: $465 per player</li>
                <li>Ice Hockey Winter: $465 per player</li>
            </ul>'],
            ['q' => 'Where are the games played?', 'a' => 'Game-day rinks and venues depend on the league and division -- see the <a href="' . $baseUrl . 'locations" data-partial class="text-primary-600 font-bold hover:underline">Locations</a> page, or check your specific season\'s schedule.'],
            ['q' => 'What are the ages within the leagues?', 'a' => '<ul class="list-disc pl-5 space-y-1.5">
                <li>Adults are 18+ (all ages are welcome, and most teams have a variety of ages per team)</li>
                <li>All 35+ leagues require the player to be turning 35 prior to the end of the current year (some exceptions may apply)</li>
            </ul>'],
            ['q' => 'What is the skill level required?', 'a' => '<ul class="list-disc pl-5 space-y-1.5">
                <li>All skill levels are welcome</li>
                <li>In most cases there will be divisions (A/B/C/D) depending on the number of teams</li>
            </ul>'],
            ['q' => 'Can I register as an individual or team?', 'a' => 'We welcome individuals, groups of players, and full teams.'],
            ['q' => 'What if I have only a partial team?', 'a' => 'We can help build out your roster -- we often have players on a waitlist looking for a team.'],
            ['q' => 'What is the refund policy?', 'a' => '<ul class="list-disc pl-5 space-y-1.5">
                <li>A $50 administrative fee applies; refunds are issued via PayPal, prorated for games already scheduled</li>
                <li>Refunds are only provided up to 25% of season completion, and only for unforeseen circumstances -- this includes significant injury, medical reasons (e.g. pregnancy), or a move from the area</li>
                <li>Refunds will not be provided after 25% of the season has been completed</li>
            </ul>'],
            ['q' => 'What is the PUC league?', 'a' => 'This league is for Persons with Unique Characteristics. Please reach out through the <a href="' . $baseUrl . 'contact" data-partial class="text-primary-600 font-bold hover:underline">Contact</a> page if you have a player who is Autistic/ASD or has other specific needs.'],
            ['q' => 'Can I join the invitational shack league?', 'a' => 'This is an invitational Coed league -- we run several open Coed leagues.'],
            ['q' => 'How do I become a sponsor?', 'a' => 'Please inquire -- we have numerous options for sponsors at varying levels. Send an email to <a href="mailto:info@canadianallstarsports.com" class="text-primary-600 font-bold hover:underline">info@canadianallstarsports.com</a> or <a href="mailto:info@cas-sports.com" class="text-primary-600 font-bold hover:underline">info@cas-sports.com</a>.'],
            ['q' => 'What is the weather cancellation policy?', 'a' => 'Please follow us on social media for live weather or cancellation updates -- <a href="https://www.facebook.com/Thornton-Ball-Hockey-League-277517455033/" target="_blank" rel="noopener" class="text-primary-600 font-bold hover:underline">Facebook</a> / <a href="https://www.instagram.com/essahockey/" target="_blank" rel="noopener" class="text-primary-600 font-bold hover:underline">Instagram</a>.'],
        ];
        ?>
        <?php foreach ($faqs as $i => $faq): ?>
            <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden" data-aos="fade-up" data-aos-duration="600" data-aos-delay="<?= min($i, 6) * 80 ?>">
                <button type="button" @click="open = (open === <?= $i ?> ? -1 : <?= $i ?>)"
                    class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left">
                    <span class="text-sm font-bold text-secondary-900 dark:text-white"><?= htmlspecialchars($faq['q']) ?></span>
                    <svg class="w-4 h-4 shrink-0 text-primary-500 transition-transform" :class="open === <?= $i ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === <?= $i ?>" x-collapse x-cloak class="px-5 pb-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed"><?= $faq['a'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
