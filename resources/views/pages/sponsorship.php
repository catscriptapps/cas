<?php
// /resources/views/pages/sponsorship.php

declare(strict_types=1);

/**
 * Guest-facing "Sponsorship" page. Ported from legacy essahockey_live's
 * sponsorship_view.php -- an intro pitch + a logo grid of current partners,
 * fully static content (no DB table, no admin CRUD in legacy either).
 * Images copied verbatim from essahockey_live/images/sponsors/ into
 * public/images/sponsors/ (the *_old sponsors folder was left behind --
 * only the 7 logos legacy's own partners() method actually references).
 */

$partners = [
    ['file' => 'km_repairs_thornton.png', 'name' => 'KM Repairs (Thornton)'],
    ['file' => 'purely_canadian_h2o_inc.png', 'name' => 'Purely Canadian H2O Inc.'],
    ['file' => 'thornton_pharmacy.png', 'name' => 'Thornton Pharmacy'],
    ['file' => 'thornton_animal_clinic.png', 'name' => 'Thornton Animal Clinic'],
    ['file' => 'tim_gilman.png', 'name' => 'Tim Gilman'],
    ['file' => 'last_shot_bar_and_grill.png', 'name' => 'The Last Shot Bar & Grill'],
    ['file' => 'tonys_barber_zone.avif', 'name' => "Tony's Barber Zone"],
];
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php
    $breadcrumbs = ['Sponsorship' => '/sponsorship'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "Sponsorship" + its NavigationConfig summary for every
    // viewer, admin included (see layout-header.php).
    ?>

    <div class="p-8 sm:p-10 rounded-3xl bg-primary-900 dark:bg-secondary-900 text-slate-100 shadow-xl border border-slate-800 relative overflow-hidden mb-14">
        <svg class="absolute right-0 bottom-0 opacity-5 w-40 h-40 translate-x-8 translate-y-8" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
        </svg>

        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Partner With Us</p>
        <h2 class="text-2xl sm:text-3xl font-black tracking-tight mb-4">Become Part of the Canadian All Star Sports Family</h2>
        <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
            We offer corporations and individuals a cost-effective and unique advertising opportunity. Sponsor a youth or adult
            team (your company logo on every jersey), or have your business featured on a banner or advertisement at the
            facility. Every sponsor is also featured right here on our website, and your sponsorship is fully tax deductible.
        </p>

        <a href="mailto:info@canadianallstarsports.com"
            class="inline-flex items-center gap-2 mt-8 px-8 py-3 rounded-full bg-secondary-400 hover:bg-primary-400 text-slate-900 hover:text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-secondary-500/20 transition-all active:scale-[0.98]">
            info@canadianallstarsports.com
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-6 pb-3 border-b border-gray-100 dark:border-gray-800">
        Our Partners
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($partners as $partner): ?>
            <div class="p-6 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col items-center text-center">
                <div class="h-28 w-full flex items-center justify-center mb-4">
                    <img src="<?= $assetBase ?>images/sponsors/<?= $partner['file'] ?>" alt="<?= htmlspecialchars($partner['name']) ?>" class="max-h-28 max-w-full object-contain">
                </div>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400"><?= htmlspecialchars($partner['name']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
