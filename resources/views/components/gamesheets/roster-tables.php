<?php
// /resources/views/components/gamesheets/roster-tables.php

use Src\Service\AuthService;

/** @var array $rosters */

$isAdmin = AuthService::isAdmin();
?>

<div class="p-4 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-800">
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

        <?php foreach (['home', 'away'] as $side): ?>
            <?php $data = $rosters[$side]; ?>
            <div class="roster-container w-full">

                <h4 class="flex items-center gap-2 mb-3 text-[10px] md:text-[11px] font-black uppercase tracking-widest text-gray-400">
                    <span class="text-primary-600 dark:text-primary-400">
                        <?= $side === 'home' ? 'HOME' : 'AWAY' ?> (<?= $rosters['game_type'] ?>)
                    </span>
                    <span class="text-gray-900 dark:text-white truncate">
                        <?= htmlspecialchars($data['name']) ?>
                    </span>
                </h4>

                <div class="overflow-x-auto">
                    <table class="roster-fixed-table" data-game-id="<?= htmlspecialchars($rosters['encoded_game_id']) ?>" data-side="<?= $side ?>">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center cursor-pointer" data-sort="number">#</th>
                                <th style="width: auto;" class="text-left px-3 cursor-pointer" data-sort="string">Player Name</th>
                                <th style="width: 35px;" class="text-center cursor-pointer" data-sort="number">Per</th>
                                <th style="width: 60px;" class="text-center cursor-pointer" data-sort="string">Time</th>
                                <th style="width: 35px;" class="text-center cursor-pointer bg-primary-50 dark:bg-primary-900/20 text-primary-700" data-sort="number">GP</th>
                                <th style="width: 35px;" class="text-center cursor-pointer" data-sort="number">G</th>
                                <th style="width: 35px;" class="text-center cursor-pointer" data-sort="number">A</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['players']) || $data['players']->isEmpty()): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-xs text-gray-400 italic py-4">No roster for this team.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($data['players'] as $player): ?>
                                <tr data-player-id="<?= $player['player_id'] ?>">
                                    <td class="stat-cell">
                                        <?php if ($player['number'] === 'G'): ?>
                                            <div class="w-full py-2 text-center font-bold text-primary-500 bg-gray-50/30 dark:bg-gray-800/30">G</div>
                                        <?php elseif ($isAdmin): ?>
                                            <input type="text" data-field="player_number" value="<?= htmlspecialchars((string)$player['number']) ?>" class="stat-input font-bold">
                                        <?php else: ?>
                                            <div class="w-full py-2 text-center font-bold"><?= htmlspecialchars((string)$player['number']) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="player-name-cell"><?= htmlspecialchars($player['name']) ?></td>

                                    <td class="stat-cell">
                                        <?php if ($isAdmin): ?>
                                            <input type="text" data-field="period" value="<?= htmlspecialchars((string)($player['period'] ?: '')) ?>" class="stat-input">
                                        <?php else: ?>
                                            <?= htmlspecialchars((string)($player['period'] ?: '')) ?>
                                        <?php endif; ?>
                                    </td>

                                    <td class="stat-cell">
                                        <?php if ($isAdmin): ?>
                                            <input type="text" data-field="time_of_goal" value="<?= htmlspecialchars((string)($player['time_of_goal'] ?: '')) ?>" class="stat-input font-mono">
                                        <?php else: ?>
                                            <?= htmlspecialchars((string)($player['time_of_goal'] ?: '')) ?>
                                        <?php endif; ?>
                                    </td>

                                    <td class="stat-cell bg-primary-50/10">
                                        <?php if ($isAdmin): ?>
                                            <input type="text" data-field="games_played" value="<?= $player['games_played'] > 0 ? $player['games_played'] : '' ?>" class="stat-input font-black text-primary-700">
                                        <?php else: ?>
                                            <?= $player['games_played'] > 0 ? $player['games_played'] : '' ?>
                                        <?php endif; ?>
                                    </td>

                                    <td class="stat-cell">
                                        <?php if ($isAdmin): ?>
                                            <input type="text" data-field="goals" value="<?= $player['goals'] > 0 ? $player['goals'] : '' ?>" class="stat-input">
                                        <?php else: ?>
                                            <?= $player['goals'] > 0 ? $player['goals'] : '' ?>
                                        <?php endif; ?>
                                    </td>

                                    <td class="stat-cell">
                                        <?php if ($isAdmin): ?>
                                            <input type="text" data-field="assists" value="<?= $player['assists'] > 0 ? $player['assists'] : '' ?>" class="stat-input">
                                        <?php else: ?>
                                            <?= $player['assists'] > 0 ? $player['assists'] : '' ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($isAdmin): ?>
        <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div id="save-status" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                <span>System Live: Auto-saving enabled</span>
            </div>

            <button type="button" title="Print PDF" class="print-section-btn flex items-center gap-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-2 py-1 rounded-md text-[10px] font-bold transition-all">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Section
            </button>
        </div>
    <?php endif; ?>
</div>

<style>
    .roster-fixed-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .dark .roster-fixed-table {
        background: #111827;
        border-color: #374151;
    }

    .roster-fixed-table th {
        padding: 8px 0;
        font-weight: 900;
        text-transform: uppercase;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
    }

    .dark .roster-fixed-table th {
        border-color: #374151;
        color: #9ca3af;
    }

    .player-name-cell {
        padding: 0 12px;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #111827;
    }

    .dark .player-name-cell {
        color: #f9fafb;
    }

    .stat-cell {
        padding: 0;
        border-left: 1px solid #f3f4f6;
        text-align: center;
    }

    .dark .stat-cell {
        border-color: #1f2937;
    }

    .stat-input {
        width: 100%;
        height: 34px;
        background: transparent;
        text-align: center;
        border: none !important;
        outline: none !important;
        font-size: 11px;
        color: #111827;
        transition: background 0.2s, color 0.2s;
    }

    .dark .stat-input {
        color: #e5e7eb;
    }

    .stat-input:focus {
        background: #f0fbfb;
        box-shadow: inset 0 0 0 1px #4dc2c1;
    }

    .dark .stat-input:focus {
        background: #0f2e2e;
        color: #ffffff;
        box-shadow: inset 0 0 0 1px #4dc2c1;
    }

    .dark .stat-input::placeholder {
        color: #4b5563;
    }

    th[data-sort]::after {
        content: ' \2195';
        font-size: 9px;
        opacity: 0.2;
    }

    .roster-fixed-table tbody tr td {
        border-bottom: 1px solid #f3f4f6;
    }

    .dark .roster-fixed-table tbody tr td {
        border-bottom: 1px solid #1f2937;
    }

    .roster-fixed-table tbody tr:last-child td {
        border-bottom: none;
    }

    .dark .text-primary-700 {
        color: #6fd3d2;
    }
</style>
