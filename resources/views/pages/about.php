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
                Built On <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-primary-500 to-secondary-500 dark:from-primary-400 dark:via-primary-300 dark:to-secondary-400">Thoroughness</span>, Not Guesswork.
            </h1>

            <p class="text-slate-600 dark:text-slate-400 max-w-xl font-medium leading-relaxed">
                Home Comfort Reports is the platform inspection companies trust to turn a completed on-site visit into a clear, professional report. Once the walkthrough is done, an inspector documents every finding through the same disciplined, standards-based structure — so what a client receives is consistent and complete, no matter which company or inspector was on site.
            </p>
        </div>

        <div class="hidden lg:block lg:col-span-5 bg-white/80 dark:bg-secondary-900/40 border border-slate-200 dark:border-secondary-800 shadow-xl rounded-2xl p-6 backdrop-blur-sm" data-aos="fade-left" data-aos-duration="800" data-aos-delay="150">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-secondary-800">
                    <span class="text-[11px] font-black text-slate-500 dark:text-secondary-500 uppercase tracking-widest">By The Numbers</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-400 border border-primary-100 dark:border-primary-900/50">
                        <i class="fa-solid fa-house-chimney-crack text-[10px] mr-1"></i> Home Inspections
                    </span>
                </div>

                <p class="text-slate-600 dark:text-secondary-400 leading-normal font-medium text-sm">
                    Every report is built from the same structured, section-by-section checklist -- so quality never depends on who happened to show up that day.
                </p>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Inspectors</span>
                        <span class="text-sm font-black text-slate-800 dark:text-white">Certified</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Report Delivery</span>
                        <span class="text-sm font-black text-primary-600 dark:text-primary-400">Digital &amp; PDF</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Evidence</span>
                        <span class="text-sm font-black text-slate-800 dark:text-white">Photo &amp; Video</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Access</span>
                        <span class="text-sm font-black text-secondary-600 dark:text-secondary-400">No Account Needed</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- How We Work -->
<section class="relative py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-white dark:bg-gray-950 transition-colors duration-300">
    <div class="max-w-2xl mx-auto relative z-10 text-center space-y-4 mb-16" data-aos="fade-up" data-aos-duration="700">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
            <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                Our Process
            </p>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            How We Work
        </h2>
        <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 font-medium leading-relaxed max-w-xl mx-auto">
            Our platform picks up the moment the on-site visit wraps -- here's what happens next.
        </p>
    </div>

    <div class="max-w-6xl mx-auto relative">
        <!-- Connecting line (desktop only) -->
        <div class="hidden lg:block absolute top-8 left-[12.5%] right-[12.5%] h-px bg-gradient-to-r from-primary-200 via-secondary-200 to-primary-200 dark:from-primary-900 dark:via-secondary-900 dark:to-primary-900"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-6 relative">
            <?php
            $steps = [
                [
                    'number'  => '01',
                    'icon'    => 'fa-right-to-bracket',
                    'title'   => 'Inspector Signs In',
                    'summary' => 'Once the walkthrough is complete, the inspector signs in to their company\'s workspace to begin documenting the visit.',
                ],
                [
                    'number'  => '02',
                    'icon'    => 'fa-camera-retro',
                    'title'   => 'Document The Findings',
                    'summary' => 'Section by section, they record what they found and attach the photo and video evidence captured during the visit.',
                ],
                [
                    'number'  => '03',
                    'icon'    => 'fa-file-shield',
                    'title'   => 'Report Finalized',
                    'summary' => 'Once documentation is complete, a polished, cover-paged PDF report is generated and a secure access code is issued.',
                ],
                [
                    'number'  => '04',
                    'icon'    => 'fa-key',
                    'title'   => 'Client Views Report',
                    'summary' => 'The client enters their access code on our home page to view and download the finished report -- no account needed.',
                ],
            ];
            ?>
            <?php foreach ($steps as $i => $step): ?>
                <div class="relative flex flex-col items-center text-center" data-aos="fade-up" data-aos-duration="700" data-aos-delay="<?= $i * 100 ?>">
                    <div class="relative z-10 h-16 w-16 rounded-2xl bg-white dark:bg-gray-900 border-2 border-primary-100 dark:border-primary-900 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-5 shadow-md">
                        <i class="fa-solid <?= $step['icon'] ?> text-xl"></i>
                    </div>
                    <span class="text-[11px] font-black uppercase tracking-widest text-secondary-500 dark:text-secondary-400 mb-1.5"><?= $step['number'] ?></span>
                    <h3 class="text-base font-black text-secondary-900 dark:text-white tracking-tight mb-2"><?= htmlspecialchars($step['title']) ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed max-w-xs"><?= htmlspecialchars($step['summary']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="relative overflow-hidden py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-slate-50 dark:bg-slate-950 transition-colors duration-300 border-t border-slate-200 dark:border-slate-800 left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="absolute -top-24 -right-24 w-[400px] h-[400px] bg-secondary-500/[0.04] dark:bg-secondary-500/[0.06] rounded-full blur-[130px] pointer-events-none"></div>

    <div class="max-w-2xl mx-auto relative z-10 text-center space-y-4 mb-14" data-aos="fade-up" data-aos-duration="700">
        <div class="inline-flex items-center justify-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-secondary-500 dark:bg-secondary-400 animate-pulse"></span>
            <p class="uppercase tracking-[0.25em] text-[10px] font-black text-secondary-600 dark:text-secondary-400">
                Why Choose Us
            </p>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            What Sets Our Reports Apart
        </h2>
    </div>

    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
        <?php
        $values = [
            [
                'icon'    => 'fa-building',
                'title'   => 'Built For Multiple Companies',
                'summary' => 'Every subscribing inspection company gets its own branded reports, cover pages, and inspector accounts -- all on one platform.',
            ],
            [
                'icon'    => 'fa-list-check',
                'title'   => 'Standards-Based, Not Ad-Hoc',
                'summary' => 'Report checklists are built against recognized standards of practice, so every property gets the same rigorous structure -- not whatever an inspector remembers to check.',
            ],
            [
                'icon'    => 'fa-bolt',
                'title'   => 'Fast Digital Turnaround',
                'summary' => 'No waiting a week for a mailed report. Once documentation is finished, your cover-paged PDF and access code are ready right away.',
            ],
            [
                'icon'    => 'fa-camera-retro',
                'title'   => 'Nothing Gets Lost',
                'summary' => 'Photos and video are attached directly to the section they belong to -- not reconstructed from memory or shuffled between apps later.',
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
            Whether you're trying to access a report or curious about bringing your company onto the platform, reach out and we'll help.
        </p>

        <a href="<?= $baseUrl ?>contact" data-partial
            class="relative inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all duration-300 active:scale-[0.98]">
            Get in Touch
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>
</section>
