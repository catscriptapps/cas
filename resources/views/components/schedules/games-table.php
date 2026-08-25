<?php
// /resources/views/components/schedules/games-table.php

use Src\Service\AuthService;
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
    // `overflow-clip`, not `overflow-hidden`, on both this card and the
    // table wrapper below -- `hidden` establishes a scroll container (even
    // though nothing here ever actually scrolls within it), which becomes
    // the sticky positioning context for the thead inside instead of the
    // page/viewport, leaving it nowhere to "stick" to. `clip` clips the
    // rounded corners identically without that side effect -- the same
    // pattern already used for the thead corners on Registrations/League
    // Management (see resources/views/components/ui/sortable-th.php callers).
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
                // Must clear the sticky page header above it (see
                // components/schedules/header.php), whose own height differs
                // by breakpoint: it stacks its action buttons under the
                // title below `md` (768px, taller), and the topbar itself
                // steps up at `sm` (640px) -- three tiers to track both
                // changes without overlapping at any width. Measured, not
                // guessed: topbar 82px/98px + page-header ~177px (stacked)
                // /~87px (single row), plus a few px of buffer.
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
                        <th class="hidden md:table-cell px-4 py-3 border-b border-gray-100 dark:border-gray-800 w-20"></th>
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
                        <th class="hidden md:table-cell px-4 py-2 w-20"></th>
                    </tr>
                </thead>
                <tbody class="schedules-tbody-js divide-y divide-gray-100 dark:divide-gray-800">
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
                                <td colspan="7" class="h-7 border-y border-gray-200 dark:border-gray-800"></td>
                            </tr>
                        <?php endif; ?>

                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors"
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

                                    <div class="md:hidden space-y-3">
                                        <div class="flex flex-col space-y-1">
                                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-none">
                                                <?= htmlspecialchars($divisionName) ?>
                                            </span>
                                            <span class="text-[11px] text-gray-600 dark:text-gray-400 font-medium">
                                                &#128205; <?= htmlspecialchars($locationDesc) ?>
                                            </span>
                                        </div>

                                        <div class="space-y-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-[8px] font-bold text-gray-400 w-3 text-center">H</span>
                                                <span class="text-xs font-bold text-gray-900 dark:text-white">
                                                    <?= htmlspecialchars($homeTeamName) ?>
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-[8px] font-bold text-gray-400 w-3 text-center">A</span>
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    <?= htmlspecialchars($awayTeamName) ?>
                                                </span>
                                            </div>
                                        </div>

                                        <?php if (AuthService::isAdmin()): ?>
                                            <div class="flex items-center space-x-4 pt-2 border-t border-gray-50 dark:border-gray-800">
                                                <button
                                                    data-action="edit-game"
                                                    data-game-id="<?= IdEncoder::encode($game->entry_id) ?>"
                                                    data-season-id="<?= IdEncoder::encode($game->season_id) ?>"
                                                    data-game-date="<?= $game->game_date->format('Y-m-d') ?>"
                                                    data-game-time="<?= $game->game_time ?>"
                                                    data-location-id="<?= $game->location ?>"
                                                    data-home-team-id="<?= $game->home ?>"
                                                    data-away-team-id="<?= $game->away ?>"
                                                    data-ref1="<?= htmlspecialchars((string)$game->referee1) ?>"
                                                    data-ref2="<?= htmlspecialchars((string)$game->referee2) ?>"
                                                    data-timekeep="<?= htmlspecialchars((string)$game->timekeep) ?>"
                                                    data-is-playoff="<?= $game->is_playoff ?>"
                                                    class="edit-schedule-btn flex items-center text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button
                                                    data-action="delete-game"
                                                    data-id="<?= IdEncoder::encode($game->entry_id) ?>"
                                                    class="delete-schedule-btn flex items-center text-[10px] font-bold uppercase tracking-wider text-red-500">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            </div>
                                        <?php endif ?>
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

                            <td class="hidden md:table-cell px-4 py-2.5 align-top text-right">
                                <?php if (AuthService::isAdmin()): ?>
                                    <div class="flex items-center justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            type="button"
                                            data-action="edit-game"
                                            class="edit-schedule-btn p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-white dark:hover:bg-gray-800 shadow-sm border border-transparent hover:border-gray-100 dark:hover:border-gray-700 transition-all"
                                            data-game-id="<?= IdEncoder::encode($game->entry_id) ?>"
                                            data-season-id="<?= IdEncoder::encode($game->season_id) ?>"
                                            data-game-date="<?= $game->game_date->format('Y-m-d') ?>"
                                            data-game-time="<?= $game->game_time ?>"
                                            data-location-id="<?= $game->location ?>"
                                            data-home-team-id="<?= $game->home ?>"
                                            data-away-team-id="<?= $game->away ?>"
                                            data-ref1="<?= htmlspecialchars((string)$game->referee1) ?>"
                                            data-ref2="<?= htmlspecialchars((string)$game->referee2) ?>"
                                            data-timekeep="<?= htmlspecialchars((string)$game->timekeep) ?>"
                                            data-is-playoff="<?= $game->is_playoff ?>"
                                            title="Edit Game">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <button
                                            data-action="delete-game"
                                            data-id="<?= IdEncoder::encode($game->entry_id) ?>"
                                            class="delete-schedule-btn p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-white dark:hover:bg-gray-800 shadow-sm border border-transparent hover:border-gray-100 dark:hover:border-gray-700 transition-all" title="Delete Game">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="no-filter-results-row hidden">
                        <td colspan="7" class="px-6 py-10 text-center text-gray-400 italic text-sm">
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
