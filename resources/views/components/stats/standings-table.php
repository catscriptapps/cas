<?php
// /resources/views/components/stats/standings-table.php

use Src\Service\AuthService;

/** @var array $groupedData */
/** @var int $seasonId */
/** @var bool $isPlayoff */

$isAdmin = AuthService::isAdmin();
?>

<?php if (empty($groupedData)): ?>
    <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm p-12 text-center text-gray-500 dark:text-gray-400">
        <p class="font-bold font-sans">No standings recorded <?= $isPlayoff ? 'for the playoffs' : 'yet' ?>.</p>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($groupedData as $groupName => $teams): ?>
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-clip">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <span class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2.5 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-black text-sm font-sans whitespace-nowrap">
                        <?= htmlspecialchars((string)$groupName) ?>
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white font-sans uppercase tracking-wide truncate">Group <?= htmlspecialchars((string)$groupName) ?></h3>
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[640px]">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                <th class="px-4 py-3 sticky left-0 bg-gray-50 dark:bg-gray-800/50 z-[5]">#</th>
                                <th class="px-4 py-3 sticky left-10 bg-gray-50 dark:bg-gray-800/50 z-[5] min-w-[160px]">Team</th>
                                <th class="px-3 py-3 text-center">W</th>
                                <th class="px-3 py-3 text-center">L</th>
                                <th class="px-3 py-3 text-center">T</th>
                                <th class="px-3 py-3 text-center">PTS</th>
                                <th class="px-3 py-3 text-center">GF</th>
                                <th class="px-3 py-3 text-center">GA</th>
                                <th class="px-3 py-3 text-center">Diff</th>
                                <th class="px-3 py-3 text-center">GP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php foreach ($teams as $rank => $team): ?>
                                <tr class="stats-team-row hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors"
                                    data-team-id="<?= (int)$team['team_id'] ?>"
                                    data-season-id="<?= (int)$seasonId ?>"
                                    data-is-playoff="<?= $isPlayoff ? 1 : 0 ?>">
                                    <td class="px-4 py-3 sticky left-0 bg-white dark:bg-gray-900 text-sm font-bold text-gray-400"><?= $rank + 1 ?></td>
                                    <td class="px-4 py-3 sticky left-10 bg-white dark:bg-gray-900 text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars((string)$team['team_name']) ?></td>

                                    <?php foreach (['wins' => 'wins', 'losses' => 'losses', 'ties' => 'ties'] as $key => $field): ?>
                                        <td class="px-3 py-3 text-center">
                                            <?php if ($isAdmin): ?>
                                                <input type="number" class="stats-input" data-field="<?= $field ?>" value="<?= (int)$team[$key] ?>" min="0">
                                            <?php else: ?>
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300"><?= (int)$team[$key] ?></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td class="px-3 py-3 text-center pts-cell text-sm font-black text-primary-600"><?= (int)$team['pts'] ?></td>

                                    <?php foreach (['gf' => 'goals_for', 'ga' => 'goals_against'] as $key => $field): ?>
                                        <td class="px-3 py-3 text-center">
                                            <?php if ($isAdmin): ?>
                                                <input type="number" class="stats-input" data-field="<?= $field ?>" value="<?= (int)$team[$key] ?>" min="0">
                                            <?php else: ?>
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300"><?= (int)$team[$key] ?></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td class="px-3 py-3 text-center diff-cell text-sm font-bold <?= $team['diff'] > 0 ? 'text-emerald-500' : ($team['diff'] < 0 ? 'text-rose-500' : 'text-gray-400') ?>">
                                        <?= $team['diff'] > 0 ? '+' . $team['diff'] : $team['diff'] ?>
                                    </td>
                                    <td class="px-3 py-3 text-center text-sm font-semibold text-gray-500"><?= (int)$team['gp'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 text-center">
            1st tie-break is wins, then head-to-head, then goals-for / goals-against differential.
        </p>
    </div>
<?php endif; ?>

<style>
    .stats-input {
        width: 3rem;
        text-align: center;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #1f2937;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 0.5rem;
        padding: 0.25rem 0;
        transition: all 0.15s ease;
    }

    .dark .stats-input {
        color: #f3f4f6;
    }

    .stats-input:hover {
        background: rgba(77, 194, 193, 0.08);
    }

    .stats-input:focus {
        outline: none;
        border-color: #4dc2c1;
        background: rgba(77, 194, 193, 0.12);
    }

    .stats-input::-webkit-outer-spin-button,
    .stats-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .stats-input[type=number] {
        -moz-appearance: textfield;
    }

    .stats-team-row.row-saving,
    .stats-player-row.row-saving {
        background-color: rgba(77, 194, 193, 0.06);
    }

    .stats-team-row.row-saved-success,
    .stats-player-row.row-saved-success {
        background-color: rgba(16, 185, 129, 0.08);
    }

    .stats-team-row.row-save-error,
    .stats-player-row.row-save-error {
        background-color: rgba(244, 63, 94, 0.08);
    }
</style>
