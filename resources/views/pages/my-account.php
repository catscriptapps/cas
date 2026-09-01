<?php
// /resources/views/pages/my-account.php

declare(strict_types=1);

use Src\Controller\MyAccountController;

/** @var string $baseUrl */

// No title/summary block here -- the shared hero above the topbar already
// shows "My Account" + its NavigationConfig summary (see layout-header.php).

$registrations = (new MyAccountController())->dashboardData();
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 lg:py-16 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

    <?php if (empty($registrations)): ?>
        <div class="text-center py-16">
            <p class="text-sm text-gray-400 font-bold">No registrations found on your account.</p>
            <a href="<?= $baseUrl ?>register" data-partial
                class="inline-flex items-center gap-2 mt-6 px-6 py-2.5 rounded-full bg-primary-500 hover:bg-primary-600 text-white font-black text-xs uppercase tracking-widest transition-all active:scale-[0.98]">
                Register Now
            </a>
        </div>
    <?php endif; ?>

    <div class="space-y-8">
        <?php foreach ($registrations as $item): ?>
            <?php
            // Fed straight into the edit modal client-side -- avoids a
            // second round-trip to re-fetch the same data this page already
            // queried. Only the editable fields the modal actually needs.
            $editableFields = [
                'entry_id' => $item['entry_id'],
                'full_name' => $item['full_name'],
                'age' => $item['age'],
                'phone' => $item['phone'],
                'address' => $item['address'],
                'city' => $item['city'],
                'province_id' => $item['province_id'],
                'postal_code' => $item['postal_code'],
                'position' => $item['position'],
                'team_name' => $item['team_name'],
                'special_requests' => $item['special_requests'],
            ];
            ?>
            <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm p-6 sm:p-8" data-registration-card data-entry-id="<?= $item['entry_id'] ?>" data-registration="<?= htmlspecialchars(json_encode($editableFields), ENT_QUOTES, 'UTF-8') ?>">

                <div class="flex flex-wrap items-start justify-between gap-4 pb-6 mb-6 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h2 class="text-lg font-black text-secondary-900 dark:text-white"><?= htmlspecialchars($item['full_name'] ?? '') ?></h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wide mt-1">
                            <?= htmlspecialchars(trim(($item['league'] ?? '') . ' -- ' . ($item['division'] ?? ''), " -")) ?: 'Division pending' ?>
                        </p>
                    </div>
                    <button type="button" data-edit-registration-btn data-entry-id="<?= $item['entry_id'] ?>"
                        class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit My Info
                    </button>
                </div>

                <!-- Status row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Payment Status</p>
                        <?php if ($item['has_paid']): ?>
                            <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">Paid -- $<?= number_format($item['amount_paid'], 2) ?></p>
                        <?php else: ?>
                            <p class="text-sm font-black text-amber-600 dark:text-amber-400">Payment Pending</p>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Team Assignment</p>
                        <?php if (!empty($item['teams'])): ?>
                            <p class="text-sm font-black text-secondary-900 dark:text-white"><?= htmlspecialchars(implode(', ', array_column($item['teams'], 'team_name'))) ?></p>
                        <?php else: ?>
                            <p class="text-sm font-black text-gray-400">Pending Team Assignment</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php foreach ($item['teams'] as $team): ?>
                    <div class="pt-6 mt-6 border-t border-gray-100 dark:border-gray-800 space-y-6">
                        <h3 class="text-sm font-black text-secondary-900 dark:text-white uppercase tracking-tight">
                            <?= htmlspecialchars($team['team_name'] ?? 'Your Team') ?>
                            <?php if ($team['is_goalie']): ?>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">Goalie</span>
                            <?php endif; ?>
                        </h3>

                        <!-- Stats row -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <?php
                            $statTiles = $team['is_goalie']
                                ? [
                                    ['label' => 'Games Played', 'value' => $team['player_stats']['games_played'] ?? 0],
                                    ['label' => 'Goals Against', 'value' => $team['player_stats']['goals_against'] ?? 0],
                                ]
                                : [
                                    ['label' => 'Goals', 'value' => $team['player_stats']['goals'] ?? 0],
                                    ['label' => 'Assists', 'value' => $team['player_stats']['assists'] ?? 0],
                                    ['label' => 'Points', 'value' => $team['player_stats']['points'] ?? 0],
                                ];
                            if ($team['team_record']) {
                                $statTiles[] = ['label' => 'Team Record', 'value' => "{$team['team_record']['wins']}-{$team['team_record']['losses']}-{$team['team_record']['ties']}"];
                            }
                            ?>
                            <?php foreach ($statTiles as $tile): ?>
                                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 text-center">
                                    <p class="text-lg font-black text-primary-600 dark:text-primary-400"><?= htmlspecialchars((string)$tile['value']) ?></p>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mt-0.5"><?= htmlspecialchars($tile['label']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Upcoming games -->
                        <?php if (!empty($team['upcoming_games'])): ?>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3">Upcoming Games</p>
                                <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                                    <?php foreach ($team['upcoming_games']->take(5) as $game): ?>
                                        <li class="flex items-center justify-between py-2.5 text-sm gap-4">
                                            <span class="font-bold text-gray-700 dark:text-gray-300">
                                                <?= $game['is_home'] ? 'vs' : '@' ?> <?= htmlspecialchars($game['opponent']) ?>
                                                <?php if ($game['is_playoff']): ?><span class="ml-1.5 text-[9px] font-black uppercase text-secondary-500">Playoffs</span><?php endif; ?>
                                            </span>
                                            <span class="text-xs text-gray-400 font-bold shrink-0 text-right">
                                                <?= htmlspecialchars($game['date'] ?? '') ?> <?= htmlspecialchars($game['time'] ?? '') ?>
                                                <?php if ($game['location']): ?><br><?= htmlspecialchars($game['location']) ?><?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Gamesheets -->
                        <?php if (!empty($team['gamesheets']) && count($team['gamesheets'])): ?>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3">Gamesheets</p>
                                <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                                    <?php foreach ($team['gamesheets']->take(5) as $sheet): ?>
                                        <li class="flex items-center justify-between py-2.5 text-sm gap-4">
                                            <span class="font-bold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($sheet['date'] ?? '') ?></span>
                                            <span class="text-xs text-gray-400 font-bold shrink-0"><?= (int)$sheet['goals'] ?>G / <?= (int)$sheet['assists'] ?>A</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
