<?php
// /resources/views/components/gamesheets/games-table.php

use App\Utils\IdEncoder;

/** @var \App\Models\Schedule[]|\Illuminate\Database\Eloquent\Collection $games */

$regularGames = $games->where('is_playoff', 0);
$playoffGames = $games->where('is_playoff', 1);

$sections = [
    ['title' => 'Regular Season', 'data' => $regularGames],
    ['title' => 'Playoffs', 'data' => $playoffGames],
];

foreach ($sections as $section):
    $currentGames = $section['data'];
    if ($currentGames->isEmpty()) continue;

    $lastDate = null;
?>

    <?php
    // `overflow-clip` (not `overflow-hidden`) on both this card and the
    // table wrapper -- required for the sticky thead below to actually
    // stick (see components/schedules/games-table.php for the full
    // explanation of this CSS gotcha).
    ?>
    <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-clip font-sans mb-12">
        <div class="p-4 md:p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                <?= $section['title'] ?>
            </h2>
        </div>

        <div class="w-full overflow-clip">
            <table class="w-full text-left border-collapse games-filterable-table">
                <?php
                // Same measured offsets as components/schedules/games-table.php
                // (identical page-header/topbar stack above this table).
                ?>
                <thead class="sticky top-[262px] sm:top-[278px] md:top-[190px] z-[30] shadow-sm">
                    <tr class="bg-gray-50 dark:bg-gray-800/80">
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-100 dark:border-gray-800 w-24">Date</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-100 dark:border-gray-800">
                            <span class="md:hidden">Game Info</span>
                            <span class="hidden md:inline">Time</span>
                        </th>
                        <th class="hidden md:table-cell px-4 py-3 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-100 dark:border-gray-800">Division</th>
                        <th class="hidden md:table-cell px-6 py-3 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-100 dark:border-gray-800">Location</th>
                        <th class="hidden md:table-cell px-6 py-3 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-100 dark:border-gray-800">Matchup</th>
                        <th class="hidden lg:table-cell px-6 py-3 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-100 dark:border-gray-800">Staff</th>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2"></th>
                        <th class="hidden md:table-cell px-4 py-2">
                            <?php $filterColumn = 'division';
                            $filterPlaceholder = 'Filter…';
                            $filterInputType = 'search';
                            include __DIR__ . '/../ui/table-filter-input.php'; ?>
                        </th>
                        <th class="hidden md:table-cell px-6 py-2">
                            <?php $filterColumn = 'location';
                            $filterPlaceholder = 'Filter…';
                            $filterInputType = 'search';
                            include __DIR__ . '/../ui/table-filter-input.php'; ?>
                        </th>
                        <th class="hidden md:table-cell px-6 py-2">
                            <?php $filterColumn = 'matchup';
                            $filterPlaceholder = 'Filter team…';
                            $filterInputType = 'search';
                            include __DIR__ . '/../ui/table-filter-input.php'; ?>
                        </th>
                        <th class="hidden lg:table-cell px-6 py-2">
                            <?php $filterColumn = 'staff';
                            $filterPlaceholder = 'Filter ref/TK…';
                            $filterInputType = 'search';
                            include __DIR__ . '/../ui/table-filter-input.php'; ?>
                        </th>
                    </tr>
                </thead>
                <tbody class="gamesheets-tbody-js divide-y divide-gray-100 dark:divide-gray-800">
                    <?php foreach ($currentGames as $game):
                        $currentDate = $game->game_date->format('Y-m-d');
                        $showDate = ($currentDate !== $lastDate);
                        $lastDate = $currentDate;

                        $divisionName = (string)($game->season->division->division ?? 'N/A');
                        $locationDesc = (string)($game->locationRelation->location_desc ?? 'TBD');
                        $homeTeamName = (string)($game->homeTeam->team_name ?? 'N/A');
                        $awayTeamName = (string)($game->awayTeam->team_name ?? 'N/A');
                        $staffText = trim(($game->referee1 ?? '') . ' ' . ($game->referee2 ?? '') . ' ' . ($game->timekeep ?? ''));

                        if ($showDate && $lastDate !== null): ?>
                            <tr class="date-separator-row bg-gray-50/30 dark:bg-gray-800/20">
                                <td colspan="6" class="h-7 border-y border-gray-200 dark:border-gray-800"></td>
                            </tr>
                        <?php endif; ?>

                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors cursor-pointer"
                            data-game-id="<?= IdEncoder::encode($game->entry_id) ?>"
                            data-filter-division="<?= htmlspecialchars($divisionName) ?>"
                            data-filter-location="<?= htmlspecialchars($locationDesc) ?>"
                            data-filter-matchup="<?= htmlspecialchars($homeTeamName . ' ' . $awayTeamName) ?>"
                            data-filter-staff="<?= htmlspecialchars($staffText) ?>">
                            <td class="px-4 py-2.5 align-top whitespace-nowrap">
                                <div class="game-date-cell pt-[2px] <?= $showDate ? '' : 'hidden' ?>" data-date-shown="<?= $showDate ? '1' : '0' ?>">
                                    <span class="text-[10px] md:text-[11px] font-black uppercase text-primary-600 dark:text-primary-400 leading-none block">
                                        <?= $game->game_date->format('D, M j') ?>
                                    </span>
                                    <span class="text-[8px] md:text-[9px] font-bold text-gray-400 dark:text-gray-500 leading-none block mt-0.5">
                                        <?= $game->game_date->format('Y') ?>
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-2.5 align-top">
                                <div class="flex flex-col space-y-2">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white leading-none whitespace-nowrap">
                                        <?= date('g:i A', strtotime((string)$game->game_time)) ?>
                                    </span>

                                    <div class="md:hidden space-y-1">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-none block">
                                            <?= htmlspecialchars($divisionName) ?>
                                        </span>
                                        <span class="text-[11px] text-gray-600 dark:text-gray-400 font-medium block">
                                            &#128205; <?= htmlspecialchars($locationDesc) ?>
                                        </span>
                                        <div class="flex items-center space-x-2 pt-1">
                                            <span class="text-[8px] font-bold text-gray-400 w-3 text-center">H</span>
                                            <span class="text-xs font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($homeTeamName) ?></span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-[8px] font-bold text-gray-400 w-3 text-center">A</span>
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($awayTeamName) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="hidden md:table-cell px-4 py-2.5 align-top">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                    <?= htmlspecialchars($divisionName) ?>
                                </span>
                            </td>

                            <td class="hidden md:table-cell px-6 py-2.5 align-top text-xs text-gray-600 dark:text-gray-400">
                                <?= htmlspecialchars($locationDesc) ?>
                            </td>

                            <td class="hidden md:table-cell px-6 py-2.5 align-top">
                                <div class="flex flex-col space-y-1 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[9px] font-bold text-gray-400 w-4 text-center">H</span>
                                        <span class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($homeTeamName) ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[9px] font-bold text-gray-400 w-4 text-center">A</span>
                                        <span class="text-gray-500 font-medium"><?= htmlspecialchars($awayTeamName) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="hidden lg:table-cell px-6 py-2.5 align-top">
                                <div class="text-[12px] text-gray-500 italic leading-tight">
                                    <b>R1:</b> <?= htmlspecialchars((string)$game->referee1) ?: '--' ?><br>
                                    <b>R2:</b> <?= htmlspecialchars((string)$game->referee2) ?: '--' ?><br>
                                    <span class="text-gray-400 font-medium"><b>TK:</b> <?= htmlspecialchars((string)$game->timekeep) ?: '--' ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="no-filter-results-row hidden">
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic text-sm">
                            No games match your filter.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<?php endforeach; ?>

<?php if ($games->isEmpty()): ?>
    <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-12 text-center text-gray-400 italic text-sm">
        No games found.
    </div>
<?php endif; ?>
