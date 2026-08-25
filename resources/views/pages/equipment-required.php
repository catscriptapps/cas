<?php
// /resources/views/pages/equipment-required.php

declare(strict_types=1);

/**
 * Guest-facing "Required Equipment" page under the League Details dropdown.
 * Ported from legacy essahockey_live's equipment_view.php -- purely static
 * content, no DB table.
 */

$sections = [
    [
        'title' => 'Ball Hockey -- Kids (Under 18)',
        'mandatory' => [
            'CSA approved helmet with full face mask / shield',
            'Gloves',
            'Stick (good condition -- no frayed or sharp edges)',
            'Running shoes',
            'Shin guards (soccer style are popular)',
        ],
        'recommended' => ['Jock / jill', 'Elbow pads'],
    ],
    [
        'title' => 'Ball Hockey -- Adults',
        'mandatory' => [
            'Gloves',
            'Stick (good condition -- no frayed or sharp edges)',
            'Running shoes',
        ],
        'recommended' => ['CSA approved helmet', 'Shin guards (soccer style are popular)', 'Jock / jill', 'Elbow pads'],
    ],
    [
        'title' => 'Ice Hockey -- Adults',
        'mandatory' => [
            'Gloves',
            'Stick (good condition -- no frayed or sharp edges)',
            'Skates',
            'CSA approved helmet',
            'Shin guards',
            'Hockey pants',
            'Jock / jill',
            'Elbow pads',
        ],
        'recommended' => ['Mouthguard', 'Shoulder pads', 'Visor or cage', 'Neck guard'],
    ],
];
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php
    $breadcrumbs = ['League Details' => '/locations', 'Required Equipment' => '/equipment-required'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <?php
    // No title/summary block here -- the shared hero above the topbar
    // already shows "Required Equipment" + its NavigationConfig summary for
    // every viewer, admin included (see layout-header.php).
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?php foreach ($sections as $section): ?>
            <div class="p-8 rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm">
                <h2 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight mb-6">
                    <?= htmlspecialchars($section['title']) ?>
                </h2>

                <p class="text-[10px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-3">Mandatory</p>
                <ul class="space-y-2.5 mb-8">
                    <?php foreach ($section['mandatory'] as $item): ?>
                        <li class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><?= htmlspecialchars($item) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Recommended</p>
                <ul class="space-y-2.5">
                    <?php foreach ($section['recommended'] as $item): ?>
                        <li class="flex items-start gap-2.5 text-sm text-gray-500 dark:text-gray-400 font-medium">
                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span><?= htmlspecialchars($item) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</div>
