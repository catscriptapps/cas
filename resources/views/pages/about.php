<?php
// /resources/views/pages/about.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */
?>

<!-- Hero -->
<section class="relative overflow-hidden bg-gradient-to-b from-primary-100 via-white to-white dark:from-slate-950 dark:via-black dark:to-black py-20 lg:py-28 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b border-slate-200 dark:border-slate-800 left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_1px,transparent_1px),linear-gradient(to_bottom,#00000003_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

    <div class="absolute -top-40 -right-20 w-[500px] h-[500px] bg-primary-500/[0.05] dark:bg-primary-500/[0.08] rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-secondary-500/[0.05] dark:bg-secondary-500/[0.06] rounded-full blur-[140px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <div class="flex flex-col space-y-5 lg:col-span-7" data-aos="fade-right" data-aos-duration="800">
            <div class="inline-flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                    Our Story
                </p>
            </div>

            <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                Built For <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-primary-500 to-secondary-500 dark:from-primary-400 dark:via-primary-300 dark:to-secondary-400">The Game.</span>
            </h1>

            <p class="text-slate-600 dark:text-slate-400 max-w-xl font-medium leading-relaxed">
                Canadian All Star Sports is the operations engine behind local hockey leagues -- from Mens divisions to
                kids leagues. We handle the scheduling, standings, and gameday logistics so organizers can focus on the
                communities they're building on the ice.
            </p>
        </div>

        <div class="hidden lg:block lg:col-span-5 bg-white/80 dark:bg-secondary-900/40 border border-slate-200 dark:border-secondary-800 shadow-xl rounded-2xl p-6 backdrop-blur-sm" data-aos="fade-left" data-aos-duration="800" data-aos-delay="150">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-secondary-800">
                    <span class="text-[11px] font-black text-slate-500 dark:text-secondary-500 uppercase tracking-widest">Since</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 border border-primary-100 dark:border-primary-900/50">
                        <i class="fa-solid fa-hockey-puck text-[10px] mr-1"></i> 2008
                    </span>
                </div>

                <p class="text-slate-600 dark:text-secondary-400 leading-normal font-medium text-sm">
                    Empowering communities through hockey -- run for players, referees, and rinks across every division we support.
                </p>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Schedules</span>
                        <span class="text-sm font-black text-slate-800 dark:text-white">Real-Time</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Standings</span>
                        <span class="text-sm font-black text-primary-600 dark:text-primary-400">Live</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Divisions</span>
                        <span class="text-sm font-black text-slate-800 dark:text-white">Ball &amp; Ice</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Focus</span>
                        <span class="text-sm font-black text-secondary-600 dark:text-secondary-400">Community</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- What We Value -->
<section class="relative overflow-hidden py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-slate-50 dark:bg-slate-950 transition-colors duration-300 border-b border-slate-200 dark:border-slate-800">
    <div class="absolute -top-24 -right-24 w-[400px] h-[400px] bg-secondary-500/[0.04] dark:bg-secondary-500/[0.06] rounded-full blur-[130px] pointer-events-none"></div>

    <div class="max-w-2xl mx-auto relative z-10 text-center space-y-4 mb-14" data-aos="fade-up" data-aos-duration="700">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-secondary-500 dark:bg-secondary-400 animate-pulse"></span>
            <p class="uppercase tracking-[0.25em] text-[10px] font-black text-secondary-600 dark:text-secondary-400">
                What Matters To Us
            </p>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            Community First, Every Season
        </h2>
    </div>

    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
        <?php
        $values = [
            [
                'icon'    => 'fa-people-roof',
                'title'   => 'Built For Every Age Group',
                'summary' => "From kids leagues to Mens 35+, every division gets the same organized scheduling and standings.",
            ],
            [
                'icon'    => 'fa-scale-balanced',
                'title'   => 'Fair, Transparent Standings',
                'summary' => 'Stats and standings are calculated straight from submitted gamesheets -- no guesswork, no favourites.',
            ],
            [
                'icon'    => 'fa-shield-heart',
                'title'   => 'Safety On The Ice',
                'summary' => 'Incident reporting and clear league contacts keep every rink accountable, every game.',
            ],
            [
                'icon'    => 'fa-users-rectangle',
                'title'   => 'Volunteers Who Show Up',
                'summary' => 'Referees, timekeepers, and team reps who keep the game running week after week.',
            ],
        ];
        ?>
        <?php foreach ($values as $i => $value): ?>
            <div class="group relative p-7 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-start gap-5"
                data-aos="fade-up" data-aos-duration="700" data-aos-delay="<?= $i * 100 ?>">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-secondary-50 dark:bg-secondary-900/20 text-secondary-600 dark:text-secondary-400 flex items-center justify-center group-hover:bg-secondary-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid <?= $value['icon'] ?> text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-secondary-900 dark:text-white tracking-tight mb-2"><?= htmlspecialchars($value['title']) ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed"><?= htmlspecialchars($value['summary']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA -->
<section class="relative py-16 px-6 sm:px-12 lg:px-24 xl:px-32">
    <div class="max-w-4xl mx-auto text-center rounded-3xl bg-primary-50/60 dark:bg-gray-900 border border-primary-100 dark:border-gray-800 shadow-xl px-8 py-14 sm:py-16 relative overflow-hidden"
        data-aos="zoom-in" data-aos-duration="700">
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-secondary-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <h2 class="relative text-2xl sm:text-3xl font-black text-secondary-900 dark:text-white tracking-tight mb-3">Have Questions?</h2>
        <p class="relative text-sm sm:text-base text-gray-600 dark:text-slate-300 font-medium leading-relaxed max-w-xl mx-auto mb-8">
            Whether it's about a league, a schedule, or getting your team involved, reach out and we'll help.
        </p>

        <a href="<?= $baseUrl ?>contact" data-partial
            class="relative inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all duration-300 active:scale-[0.98]">
            Get in Touch
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>
</section>
