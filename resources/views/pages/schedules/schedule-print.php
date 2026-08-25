<?php
// /resources/views/pages/schedules/schedule-print.php

/**
 * @var string $companyName
 * @var \App\Models\Season $season
 * @var \Illuminate\Database\Eloquent\Collection $games
 * @var array $teams
 * @var bool $isViewAll
 */

$regularGames = $games->where('is_playoff', 0);
$playoffGames = $games->where('is_playoff', 1);

$sections = [
    ['title' => 'Regular Season', 'data' => $regularGames],
    ['title' => 'Playoffs', 'data' => $playoffGames],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'quicksand', sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        .table-reset {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .header-table {
            margin-bottom: 25px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #111827;
        }

        .doc-title {
            font-size: 24pt;
            font-weight: bold;
            color: #4dc2c1;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 1px;
            vertical-align: top;
        }

        .text-muted {
            color: #6b7280;
            font-size: 9pt;
        }

        .season-banner {
            background-color: #f0fbfb;
            border-right: 4px solid #bad767;
            padding: 10px 15px;
            text-align: right;
            margin-bottom: 20px;
        }

        .banner-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #33a7a7;
            font-weight: 700;
        }

        .banner-title {
            font-size: 14pt;
            font-weight: 700;
            color: #214a4b;
        }

        .section-header {
            font-size: 12pt;
            font-weight: 800;
            color: #214a4b;
            text-transform: uppercase;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            margin-bottom: 30px;
        }

        .team-label {
            font-weight: bold;
        }

        .schedule-table th {
            background-color: #214a4b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1px;
            padding: 12px 10px;
            text-align: left;
            border: 1px solid #214a4b;
        }

        .schedule-table td {
            padding: 10px;
            font-size: 10pt;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .date-cell {
            font-weight: bold !important;
            color: #33a7a7;
            width: 15%;
        }

        .date-cell .date-year {
            display: block;
            font-weight: 700;
            font-size: 7pt;
            color: #9ca3af;
        }

        .time-text {
            font-weight: 800;
            color: #111827;
            white-space: nowrap;
        }

        .division-badge {
            display: inline-block;
            padding: 3px;
            font-size: 5pt;
            color: #000000;
        }

        .team-row {
            margin-bottom: 4px;
        }

        .team-label {
            font-size: 7pt;
            font-weight: bold;
            color: #9ca3af;
            width: 15px;
            display: inline-block;
        }

        .team-name {
            font-weight: 600;
        }

        .home-team {
            font-weight: 800;
            color: #214a4b;
        }

        .staff-info {
            color: #6b7280;
            font-style: italic;
            white-space: nowrap;
        }

        .date-separator {
            background-color: #f7f9ee;
            height: 5px;
        }

        .teams-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .teams-table th {
            background-color: #f9fafb;
            color: #374151;
            font-size: 9px;
            text-transform: uppercase;
            padding: 10px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        .teams-table td {
            padding: 8px 10px;
            font-size: 9pt;
            border-bottom: 1px solid #f3f4f6;
        }

        .roster-count {
            color: #059669;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <table class="table-reset header-table">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div class="company-name"><?= htmlspecialchars((string)$companyName) ?></div>
                <div class="text-muted">Official League Game Schedule</div>
            </td>
            <td style="width: 50%;" class="doc-title">
                Schedule
            </td>
        </tr>
    </table>

    <div class="season-banner">
        <span class="banner-label"><?= $isViewAll ? 'Full League' : 'Active Season' ?></span><br>
        <span class="banner-title">
            <?php if ($isViewAll): ?>
                All Divisions
            <?php else: ?>
                <?= htmlspecialchars((string)($season->division->division ?? 'Unknown')) ?>

                <span style="color: #6b7280; font-weight: 300;">
                    <?= htmlspecialchars((string)($season->season_year ?? '')) ?>
                </span>
            <?php endif; ?>
        </span>
    </div>

    <?php if (!$isViewAll && !empty($teams)): ?>
        <div class="section-header">Registered Teams</div>
        <table class="teams-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Team Name</th>
                    <th style="width: 20%;">Group</th>
                    <th style="width: 25%;">Representative</th>
                    <th style="width: 15%; text-align: center;">Roster</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $team): ?>
                    <tr>
                        <td class="team-name" style="color: #111827;"><?= htmlspecialchars((string)$team['team_name']) ?></td>
                        <td><span style="font-size: 8pt; background: #f3f4f6; padding: 2px 5px; border-radius: 3px;"><?= htmlspecialchars((string)$team['group_name']) ?></span></td>
                        <td><?= htmlspecialchars((string)$team['rep_name']) ?></td>
                        <td style="text-align: center;" class="roster-count"><?= $team['player_count'] ?> Players</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($games->isEmpty()): ?>
        <table class="schedule-table">
            <tbody>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #9ca3af;">No games scheduled.</td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <?php foreach ($sections as $section):
            $currentGames = $section['data'];
            if ($currentGames->isEmpty()) continue;
            $lastDate = null;
        ?>
            <div class="section-header" <?= $section['title'] === 'Playoffs' ? ' style="page-break-before: always;"' : '' ?>><?= $section['title'] ?></div>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Division</th>
                        <th>Location</th>
                        <th>Matchup</th>
                        <th>Staff</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentGames as $game):
                        $currentDate = $game->game_date->format('Y-m-d');
                        $showDate = ($currentDate !== $lastDate);
                        $lastDate = $currentDate;

                        if ($showDate && $lastDate !== null): ?>
                            <tr class="date-separator">
                                <td colspan="6"></td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <td class="date-cell">
                                <?php if ($showDate): ?>
                                    <?= $game->game_date->format('D, M j') ?>
                                    <span class="date-year"><?= $game->game_date->format('Y') ?></span>
                                <?php endif; ?>
                            </td>

                            <td class="time-text">
                                <?= date('g:i A', strtotime((string)$game->game_time)) ?>
                            </td>
                            <td class="division-badge">
                                <div>
                                    <?= htmlspecialchars((string)($game->season->division->division ?? 'N/A')) ?>
                                </div>
                            </td>
                            <td style="font-size: 9pt;">
                                <?= htmlspecialchars((string)($game->locationRelation->location_desc ?? 'TBD')) ?>
                            </td>
                            <td>
                                <div class="team-row">
                                    <span class="team-label">H</span>
                                    <span class="team-name home-team"><?= htmlspecialchars((string)($game->homeTeam->team_name ?? 'N/A')) ?></span>
                                </div>
                                <div class="team-row">
                                    <span class="team-label">A</span>
                                    <span class="team-name"><?= htmlspecialchars((string)($game->awayTeam->team_name ?? 'N/A')) ?></span>
                                </div>
                            </td>
                            <td class="staff-info">
                                <b>R1</b>: <?= htmlspecialchars((string)$game->referee1) ?: '--' ?><br>
                                <b>R2</b>: <?= htmlspecialchars((string)$game->referee2) ?: '--' ?><br>
                                <b>TK</b>: <?= htmlspecialchars((string)$game->timekeep) ?: '--' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="text-align: right; font-size: 8pt; color: #9ca3af; font-weight: bold; margin-top: 20px;">
        Generated on <?= date('F j, Y') ?> &bull; Canadian All Star Sports
    </div>

</body>

</html>
