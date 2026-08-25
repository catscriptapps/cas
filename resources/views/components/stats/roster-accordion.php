<?php
// /resources/views/components/stats/roster-accordion.php

use Src\Service\AuthService;

/** @var \Illuminate\Support\Collection $rosters */
/** @var int $seasonId */

$isAdmin = AuthService::isAdmin();
$cleanVal = fn($val) => ($val === null || (int)$val === 0) ? '' : $val;
?>

<?php if ($rosters->isEmpty()): ?>
    <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm p-12 text-center text-gray-500 dark:text-gray-400">
        <p class="font-bold font-sans">No rosters recorded for this season.</p>
    </div>
<?php else: ?>
    <div>
        <h2 class="text-sm font-bold text-gray-900 dark:text-white font-sans uppercase tracking-wide mb-3">Player Statistics</h2>

        <div class="space-y-3">
            <?php foreach ($rosters as $team): ?>
                <?php
                $skaters = $team['skaters'];
                $goalies = $team['goalies'];
                $playerCount = $skaters->count() + $goalies->count();
                ?>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-clip">
                    <button type="button"
                        class="roster-accordion-toggle w-full flex items-center justify-between px-6 py-4 text-left"
                        onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.accordion-icon').classList.toggle('rotate-45');">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-gray-900 dark:text-white font-sans"><?= htmlspecialchars((string)$team['team_name']) ?></span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400 border border-primary-100 dark:border-primary-800">
                                <?= $playerCount ?> Players
                            </span>
                        </div>
                        <svg class="accordion-icon w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>

                    <div class="hidden border-t border-gray-100 dark:border-gray-800">
                        <div class="w-full overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[560px]">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                        <th class="px-4 py-2.5">Player</th>
                                        <th class="px-3 py-2.5 text-center">GP</th>
                                        <th class="px-3 py-2.5 text-center">G</th>
                                        <th class="px-3 py-2.5 text-center">A</th>
                                        <th class="px-3 py-2.5 text-center">PTS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <?php foreach ($skaters as $player): ?>
                                        <?php
                                        $stat = $player->stats->first();
                                        $gp = $stat->games_played ?? 0;
                                        $g = $stat->goals ?? 0;
                                        $a = $stat->assists ?? 0;
                                        $pts = $g + $a;
                                        $playerName = $player->profile->full_name ?? 'Unknown Player';
                                        ?>
                                        <tr class="stats-player-row hover:bg-gray-50 dark:hover:bg-gray-800/40"
                                            data-player-id="<?= (int)$player->player_id ?>"
                                            data-team-id="<?= (int)$team['team_id'] ?>"
                                            data-season-id="<?= (int)$seasonId ?>">
                                            <td class="px-4 py-2.5 text-sm font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars((string)$playerName) ?></td>
                                            <td class="px-3 py-2.5 text-center">
                                                <?php if ($isAdmin): ?>
                                                    <input type="number" class="stats-input" data-field="games_played" value="<?= (int)$gp ?>" min="0">
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400"><?= $cleanVal($gp) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <?php if ($isAdmin): ?>
                                                    <input type="number" class="stats-input" data-field="goals" value="<?= (int)$g ?>" min="0">
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400"><?= $cleanVal($g) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                <?php if ($isAdmin): ?>
                                                    <input type="number" class="stats-input" data-field="assists" value="<?= (int)$a ?>" min="0">
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400"><?= $cleanVal($a) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-2.5 text-center player-pts-cell text-sm font-black text-primary-600"><?= $pts ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if ($goalies->isNotEmpty()): ?>
                                        <tr>
                                            <td colspan="5" class="px-4 py-1.5 bg-gray-800 dark:bg-black text-white text-[9px] font-bold uppercase tracking-widest">Goalies</td>
                                        </tr>
                                        <?php foreach ($goalies as $player): ?>
                                            <?php
                                            $stat = $player->stats->first();
                                            $gp = $stat->games_played ?? 0;
                                            $ga = $stat->goals_against ?? 0;
                                            $sog = $stat->shots_on_goal ?? 0;
                                            $gaa = $gp > 0 ? round($ga / $gp, 2) : 0;
                                            $playerName = $player->profile->full_name ?? 'Unknown Player';
                                            ?>
                                            <tr class="stats-player-row hover:bg-gray-50 dark:hover:bg-gray-800/40"
                                                data-player-id="<?= (int)$player->player_id ?>"
                                                data-team-id="<?= (int)$team['team_id'] ?>"
                                                data-season-id="<?= (int)$seasonId ?>"
                                                data-is-goalie="1">
                                                <td class="px-4 py-2.5 text-sm font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars((string)$playerName) ?></td>
                                                <td class="px-3 py-2.5 text-center">
                                                    <?php if ($isAdmin): ?>
                                                        <input type="number" class="stats-input" data-field="games_played" value="<?= (int)$gp ?>" min="0">
                                                    <?php else: ?>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400"><?= $cleanVal($gp) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-2.5 text-center">
                                                    <?php if ($isAdmin): ?>
                                                        <input type="number" class="stats-input" data-field="goals_against" value="<?= (int)$ga ?>" min="0">
                                                    <?php else: ?>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400"><?= $cleanVal($ga) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-2.5 text-center gaa-cell text-sm text-gray-500 dark:text-gray-400"><?= $gaa > 0 ? number_format((float)$gaa, 2) : '' ?></td>
                                                <td class="px-3 py-2.5 text-center">
                                                    <?php if ($isAdmin): ?>
                                                        <input type="number" class="stats-input" data-field="shots_on_goal" value="<?= (int)$sog ?>" min="0">
                                                    <?php else: ?>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400"><?= $cleanVal($sog) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
