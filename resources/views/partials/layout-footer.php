<?php
// /resources/views/partials/layout-footer.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */
?>

<!-- Secondary Brand Theme Footer Terminal Grid -->
<footer class="bg-black border-t border-slate-900 pt-8 pb-5 mt-auto transition-colors duration-300 text-slate-400 font-medium">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">

        <!-- Main Structured Directory Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pb-6 border-b border-slate-900">

            <!-- Column 1: Corporate Profile Identity -->
            <div class="space-y-3">
                <div class="flex items-center space-x-3 group/logo">
                    <span class="font-black text-white tracking-tight text-lg"><?= htmlspecialchars($appName) ?></span>
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm leading-relaxed font-normal">
                    Empowering communities through hockey since 2008 -- schedules, stats, and standings for every league we run.
                </p>
            </div>

            <!-- Column 2: Corporate Ecosystem -->
            <div class="sm:justify-self-center">
                <h3 class="text-[11px] font-black text-white uppercase tracking-[0.2em] mb-3">Ecosystem</h3>
                <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs">
                    <?php foreach (['Home' => '', 'About' => 'about', 'Contact' => 'contact'] as $title => $slug): ?>
                        <a href="<?= $baseUrl . $slug ?>" data-partial data-title="<?= $title ?>" class="relative text-slate-400 hover:text-white transition-colors duration-200 w-fit before:absolute before:-bottom-0.5 before:left-0 before:w-0 before:h-px before:bg-primary-500 hover:before:w-full before:transition-all before:duration-300">
                            <?= $title ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php
            // Ported from legacy essahockey_live's footer "SOCIALS" widget.
            // YouTube's URL there points at the bare site root, not a real
            // channel -- ported as-is (it's a working link, just generic).
            ?>
            <div class="sm:justify-self-end">
                <h3 class="text-[11px] font-black text-white uppercase tracking-[0.2em] mb-3">Socials</h3>
                <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs">
                    <?php
                    $socials = [
                        'Facebook' => 'https://www.facebook.com/Thornton-Ball-Hockey-League-277517455033/',
                        'Instagram' => 'https://www.instagram.com/essahockey/',
                        'YouTube' => 'https://www.youtube.com/',
                    ];
                    ?>
                    <?php foreach ($socials as $title => $url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener" class="relative text-slate-400 hover:text-white transition-colors duration-200 w-fit before:absolute before:-bottom-0.5 before:left-0 before:w-0 before:h-px before:bg-primary-500 hover:before:w-full before:transition-all before:duration-300">
                            <?= $title ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- System Architecture & Trademark Alignment Base -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-5 text-[11px] text-slate-500 font-normal">
            <div class="tracking-tight">
                &copy; <?= date('Y') ?> <span class="font-bold text-slate-300"><?= htmlspecialchars($appName) ?></span> &bull; All rights reserved.
            </div>

            <div class="flex items-center gap-1.5 opacity-60 hover:opacity-100 transition-opacity duration-300">
                <a href="<?= $baseUrl ?>login" data-login-button class="font-black uppercase tracking-[0.2em] text-[9px] text-slate-500 hover:text-slate-300 transition-colors">
                    Staff Login
                </a>
            </div>
        </div>

    </div>
</footer>