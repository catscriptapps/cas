<?php
// /resources/views/pages/home.php

declare(strict_types=1);

/** @var string $baseUrl  */
?>

<section class="relative pb-12 sm:pb-16">
    <div class="max-w-5xl mx-auto">
        <div class="relative grid grid-cols-1 md:grid-cols-2 rounded-b-[2rem] sm:rounded-b-[2.5rem] overflow-hidden shadow-2xl border border-t-0 border-gray-100 dark:border-gray-800"
            data-aos="fade-up" data-aos-duration="700">

            <!-- Clients: retrieve report by access code -->
            <div class="relative overflow-hidden bg-white dark:bg-gray-900 p-6 sm:p-7 lg:p-8 flex flex-col">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-primary-500/[0.06] dark:bg-primary-500/[0.08] rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative flex flex-col items-center text-center flex-1">
                    <div class="h-11 w-11 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-3 shrink-0">
                        <i class="fa-solid fa-key text-base"></i>
                    </div>

                    <h2 class="text-lg font-black text-secondary-900 dark:text-white tracking-tight mb-1.5">Have a Report Waiting?</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-4 max-w-xs">
                        Enter the access code your inspector provided to view and download your completed inspection report.
                    </p>

                    <form id="access-code-form" class="w-full mt-auto space-y-2.5" novalidate>
                        <label for="access-code-input" class="sr-only">Access Code</label>
                        <input type="text" id="access-code-input" name="access_code" required
                            placeholder="Enter Access Code"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white text-center font-bold tracking-wider placeholder-gray-400 placeholder:font-medium placeholder:tracking-normal focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none">
                        <button type="submit"
                            class="group w-full inline-flex items-center justify-center gap-2 py-3 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all duration-300 active:scale-[0.98]">
                            Get My Report
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Divider + "OR" badge: horizontal stack on mobile, vertical split on desktop -->
            <div class="md:hidden flex items-center gap-3 px-8 py-1 bg-white dark:bg-gray-900">
                <span class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-300 dark:text-gray-600">Or</span>
                <span class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></span>
            </div>
            <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-px bg-gray-100 dark:bg-gray-800 pointer-events-none"></div>
            <div class="hidden md:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 h-11 w-11 rounded-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 items-center justify-center text-[10px] font-black uppercase tracking-widest text-gray-300 dark:text-gray-600 shadow-md z-10">
                Or
            </div>

            <!-- Returning users: sign in -->
            <div class="relative overflow-hidden bg-secondary-50/60 dark:bg-gray-900 p-6 sm:p-7 lg:p-8 flex flex-col">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-secondary-500/[0.08] dark:bg-secondary-500/[0.08] rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative flex flex-col items-center text-center flex-1">
                    <div class="h-11 w-11 rounded-xl bg-secondary-100 dark:bg-secondary-900/20 text-secondary-600 dark:text-secondary-400 flex items-center justify-center mb-3 shrink-0">
                        <i class="fa-solid fa-right-to-bracket text-base"></i>
                    </div>

                    <h2 class="text-lg font-black text-secondary-900 dark:text-white tracking-tight mb-1.5">Welcome Back</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-4 max-w-xs">
                        Sign in to manage inspections, review reports, and pick up right where you left off.
                    </p>

                    <a href="<?= $baseUrl ?>login" data-login-button title="Sign in to continue"
                        class="group w-full mt-auto inline-flex items-center justify-center gap-2 py-3 px-6 rounded-xl bg-secondary-500 hover:bg-secondary-600 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-secondary-500/20 transition-all duration-300 active:scale-[0.98]">
                        Sign In
                        <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="relative overflow-hidden bg-slate-50 dark:bg-slate-950 py-20 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b border-slate-200 dark:border-slate-800 left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="absolute -top-24 -left-24 w-[400px] h-[400px] bg-primary-500/[0.04] dark:bg-primary-500/[0.06] rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-[400px] h-[400px] bg-secondary-500/[0.04] dark:bg-secondary-500/[0.06] rounded-full blur-[130px] pointer-events-none"></div>

    <div class="max-w-2xl mx-auto relative z-10 text-center space-y-4 mb-14" data-aos="fade-up" data-aos-duration="700">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
            <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                How It Works
            </p>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            Built For <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-600 dark:from-primary-400 dark:to-secondary-400">Total Clarity</span>
        </h2>
        <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 font-medium leading-relaxed max-w-xl mx-auto">
            Every inspection follows the same disciplined process, from the first walkthrough to the final report.
        </p>
    </div>

    <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
        <?php
        $features = [
            [
                'icon'    => 'fa-user-shield',
                'title'   => 'Certified Inspectors',
                'summary' => 'Every inspection is conducted by a certified, detail-obsessed inspector who knows exactly what to look for.',
            ],
            [
                'icon'    => 'fa-list-check',
                'title'   => 'Room-By-Room Checklists',
                'summary' => 'Structured, standards-based sections walk through every system in the home — nothing gets missed.',
            ],
            [
                'icon'    => 'fa-camera-retro',
                'title'   => 'Photo & Video Evidence',
                'summary' => 'High-resolution photos and video are captured on-site and attached directly to the relevant section.',
            ],
            [
                'icon'    => 'fa-file-shield',
                'title'   => 'Polished PDF Reports',
                'summary' => 'A professional, cover-paged report is generated the moment the inspection wraps — ready to share or download.',
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
