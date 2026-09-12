<?php
// /resources/views/pages/locations.php

declare(strict_types=1);

use App\Utils\IdEncoder;
use Src\Controller\VenuesController;
use Src\Service\AuthService;

/**
 * Guest-facing "Locations" page under the League Details dropdown. Ported
 * from legacy essahockey_live's locations_view.php -- was fully static
 * (no DB table, no admin CRUD anywhere in legacy); this app adds a `venues`
 * table plus full admin add/edit/delete/reorder controls, kept separate per
 * sport group (see VenuesController / Venue model). Not to be confused with
 * the unrelated `locations` table/model already in this app, which is just
 * a short-code lookup for the Schedules form's rink dropdown.
 */

$isAdmin = AuthService::isAdmin();
$grouped = (new VenuesController())->getAll();

$sections = [
    'ball' => ['label' => 'Ball Hockey Locations', 'venues' => $grouped['ball']],
    'ice' => ['label' => 'Ice Hockey Locations', 'venues' => $grouped['ice']],
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

    <?php if ($isAdmin): ?>
        <p class="text-xs text-gray-400 font-medium mb-8">
            Click a venue's move icon, then another venue's move icon (within the same section), to swap their order.
        </p>
    <?php endif; ?>

    <div class="space-y-14" id="venues-sections">
        <?php foreach ($sections as $sport => $section): ?>
            <div data-sport-section="<?= $sport ?>">
                <div class="flex items-center justify-between gap-4 mb-6 pb-3 border-b border-gray-100 dark:border-gray-800">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight">
                        <?= htmlspecialchars($section['label']) ?>
                    </h2>
                    <?php if ($isAdmin): ?>
                        <button type="button" data-add-venue="<?= $sport ?>"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white font-black text-[11px] uppercase tracking-widest shadow-md transition-all active:scale-[0.98] shrink-0">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            Add Venue
                        </button>
                    <?php endif; ?>
                </div>

                <div class="venues-grid grid grid-cols-1 sm:grid-cols-2 gap-6" data-sport-grid="<?= $sport ?>">
                    <?php foreach ($section['venues'] as $venue): ?>
                        <div class="venue-card group relative rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden"
                            data-venue-id="<?= htmlspecialchars($venue['encoded_id']) ?>">
                            <?php if ($isAdmin): ?>
                                <div class="absolute top-3 right-3 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                    <button type="button" data-action="reorder-venue" title="Move" aria-label="Move venue"
                                        class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-700 hover:bg-gray-800 text-white shadow-md">
                                        <i class="fa-solid fa-arrows-up-down-left-right text-xs"></i>
                                    </button>
                                    <button type="button" data-action="edit-venue" title="Edit" aria-label="Edit venue"
                                        class="h-8 w-8 flex items-center justify-center rounded-full bg-slate-600 hover:bg-slate-700 text-white shadow-md">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <button type="button" data-action="delete-venue" title="Delete" aria-label="Delete venue"
                                        class="h-8 w-8 flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($venue['image'])): ?>
                                <button type="button" data-preview-venue data-venue-src="<?= $assetBase . htmlspecialchars($venue['image']) ?>"
                                    class="block w-full h-40 cursor-zoom-in">
                                    <img src="<?= $assetBase . htmlspecialchars($venue['image']) ?>" alt="<?= htmlspecialchars($venue['name']) ?>"
                                        class="w-full h-40 object-cover pointer-events-none" loading="lazy">
                                </button>
                            <?php else: ?>
                                <div class="w-full h-40 flex items-center justify-center bg-gray-50 dark:bg-gray-950 text-gray-300 dark:text-gray-700">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <div class="p-6" data-venue-text>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white" data-field="name"><?= htmlspecialchars($venue['name']) ?></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1" data-field="address"><?= htmlspecialchars((string)$venue['address']) ?></p>
                                <?php if (!empty($venue['directions'])): ?>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mt-2 leading-relaxed" data-field="directions"><?= htmlspecialchars($venue['directions']) ?></p>
                                <?php else: ?>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium mt-2 leading-relaxed" data-field="directions" hidden></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($section['venues'])): ?>
                    <p class="no-venues-message text-sm text-gray-400 font-medium italic text-center py-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl">
                        No venues yet<?= $isAdmin ? ' -- click "Add Venue" to add the first one.' : '.' ?>
                    </p>
                <?php endif; ?>
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
