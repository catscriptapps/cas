<?php
// /resources/views/pages/gamesheets/gamesheet-print.php

/**
 * Rendered once per `$mode` and fed to mPDF via a SEPARATE WriteHTML() call
 * per game (see server/api/gamesheets-pdf.php) rather than one giant string
 * for the whole season -- a full season's worth of games x two roster
 * tables each can exceed PHP's `pcre.backtrack_limit` inside mPDF's
 * regex-based HTML parser if written in a single call. Chunking avoids
 * depending on a raised ini limit that may not be available on shared
 * hosting.
 *
 * @var string $mode 'header' | 'game' | 'footer'
 * @var string $companyName (header mode)
 * @var \App\Models\Season $season (header mode)
 * @var array $teams (header mode)
 * @var \App\Models\Schedule $game (game mode)
 * @var array $rosterData (game mode) ['home' => [...], 'away' => [...]]
 */

$mode = $mode ?? 'header';

switch ($mode):
    case 'header': ?>
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

                .game-page-break {
                    page-break-before: always;
                }

                .game-row-header {
                    background-color: #f9fafb;
                    border: 1px solid #e5e7eb;
                    margin-top: 15px;
                }

                .game-info-cell {
                    padding: 8px 10px;
                    font-size: 9pt;
                    border-right: 1px solid #e5e7eb;
                }

                .label-tiny {
                    font-size: 7pt;
                    font-weight: 900;
                    color: #9ca3af;
                    text-transform: uppercase;
                    display: block;
                }

                .val-bold {
                    font-weight: bold;
                    color: #111827;
                }

                .val-teal {
                    color: #33a7a7;
                    font-weight: 800;
                }

                .roster-wrapper {
                    width: 100%;
                    border: 1px solid #e5e7eb;
                    border-top: none;
                    margin-bottom: 35px;
                    page-break-inside: avoid;
                }

                .roster-column {
                    width: 50%;
                    vertical-align: top;
                    padding: 10px;
                    border-right: 1px solid #f3f4f6;
                }

                .roster-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 8.5pt;
                }

                .roster-table th {
                    text-align: left;
                    padding: 5px;
                    border-bottom: 1px solid #e5e7eb;
                    color: #9ca3af;
                    font-weight: 900;
                    text-transform: uppercase;
                    font-size: 6.5pt;
                }

                .roster-table td {
                    padding: 5px 4px;
                    border-bottom: 1px solid #f9fafb;
                }

                .side-label {
                    color: #214a4b;
                    font-weight: bold;
                    font-size: 10pt;
                    margin-bottom: 5px;
                    text-transform: uppercase;
                    border-left: 10px solid #bad767;
                    padding-left: 5px;
                }

                .num-col {
                    width: 25px;
                    font-weight: bold;
                    color: #33a7a7;
                    text-align: center;
                }

                .stat-box {
                    min-width: 22px;
                    font-weight: 700;
                    color: #111827;
                    text-align: center;
                    font-size: 8.5pt;
                }

                .input-box {
                    border: 1px solid #e5e7eb;
                    height: 18px;
                    text-align: center;
                    font-weight: bold;
                    font-size: 8pt;
                }
            </style>
        </head>

        <body>

            <table class="table-reset header-table">
                <tr>
                    <td>
                        <div class="company-name"><?= htmlspecialchars((string)$companyName) ?></div>
                        <div style="font-size: 9pt; color: #6b7280;">Official League Gamesheets</div>
                    </td>
                    <td class="doc-title">Gamesheets</td>
                </tr>
            </table>

            <div class="season-banner">
                <span class="banner-label">Active Season</span><br>
                <span class="banner-title">
                    <?= htmlspecialchars(($season->division->division ?? 'Unknown') . ' ' . ($season->season_year ?? '')) ?>
                </span>
            </div>

            <?php if (!empty($teams)): ?>
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
                                <td style="font-weight: 700; color: #111827;"><?= htmlspecialchars((string)$team['team_name']) ?></td>
                                <td><span style="font-size: 8pt; background: #f3f4f6; padding: 2px 5px; border-radius: 3px;"><?= htmlspecialchars((string)$team['group_name']) ?></span></td>
                                <td><?= htmlspecialchars((string)$team['rep_name']) ?></td>
                                <td style="text-align: center; font-weight: bold; color: #33a7a7;"><?= $team['player_count'] ?> Players</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php break;

    case 'game': ?>
            <div class="game-page-break">
                <table class="table-reset game-row-header">
                    <tr>
                        <td class="game-info-cell" style="width: 18%;">
                            <span class="label-tiny">Date</span>
                            <span class="val-teal"><?= $game->game_date->format('D, M j') ?></span><br>
                            <span class="label-tiny">Time&nbsp;&nbsp;</span><span style="font-size: 8pt; font-weight: bold;"><?= date('g:i A', strtotime((string)$game->game_time)) ?></span>
                        </td>
                        <td class="game-info-cell">
                            <span class="label-tiny">Location</span>
                            <span style="font-size: 9pt;"> <?= htmlspecialchars((string)($game->locationRelation->location_desc ?? 'TBD')) ?></span><br>
                            <span class="label-tiny">Matchup&nbsp;</span>
                            <span class="val-bold"><?= htmlspecialchars((string)($game->homeTeam->team_name ?? 'Home')) ?></span> vs <span class="val-bold"><?= htmlspecialchars((string)($game->awayTeam->team_name ?? 'Away')) ?></span>
                        </td>
                        <td class="game-info-cell" style="width: 22%; border-right: none;">
                            <span class="label-tiny">Staffing</span>
                            <span style="font-size: 7.5pt; color: #6b7280;">
                                <b>R1:</b> <?= htmlspecialchars((string)($game->referee1 ?? '')) ?: '--' ?> |
                                <b>R2:</b> <?= htmlspecialchars((string)($game->referee2 ?? '')) ?: '--' ?><br>
                                <b>TK:</b> <?= htmlspecialchars((string)($game->timekeep ?? '')) ?: '--' ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <table class="table-reset roster-wrapper">
                    <tr>
                        <?php foreach (['home', 'away'] as $side): ?>
                            <td class="roster-column" <?= $side === 'away' ? 'style="border-right: none;"' : '' ?>>
                                <div class="side-label">&nbsp;&nbsp;<?= strtoupper($side) ?>: <?= htmlspecialchars((string)($rosterData[$side]['name'] ?? 'Team')) ?></div>
                                <table class="roster-table">
                                    <thead>
                                        <tr>
                                            <th class="num-col">#</th>
                                            <th>Player Name</th>
                                            <th style="width: 25px; text-align: center;">PER</th>
                                            <th style="width: 35px; text-align: center;">TIME</th>
                                            <th style="width: 22px; text-align: center;">GP</th>
                                            <th style="width: 22px; text-align: center;">G</th>
                                            <th style="width: 22px; text-align: center;">A</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($rosterData[$side]['players']) && $rosterData[$side]['players']->isNotEmpty()): ?>
                                            <?php foreach ($rosterData[$side]['players'] as $p): ?>
                                                <tr>
                                                    <td class="num-col"><?= htmlspecialchars((string)($p['number'] ?? '')) ?></td>
                                                    <td style="font-weight: 600;"><?= htmlspecialchars((string)($p['name'] ?? 'Unknown')) ?></td>
                                                    <td class="input-box"><?= $p['period'] ? htmlspecialchars((string)$p['period']) : '' ?></td>
                                                    <td class="input-box"><?= $p['time_of_goal'] ? htmlspecialchars((string)$p['time_of_goal']) : '' ?></td>
                                                    <td class="stat-box"><?= $p['games_played'] ? (int)$p['games_played'] : '' ?></td>
                                                    <td class="stat-box"><?= $p['goals'] ? (int)$p['goals'] : '' ?></td>
                                                    <td class="stat-box"><?= $p['assists'] ? (int)$p['assists'] : '' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" style="text-align:center; padding: 10px; color: #9ca3af;">No roster data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        <?php break;

    case 'footer': ?>
            <div style="text-align: right; font-size: 8pt; color: #9ca3af; margin-top: 20px; font-weight: bold;">
                Generated on <?= date('F j, Y') ?> &bull; <?= htmlspecialchars((string)($companyName ?? '')) ?>
            </div>

        </body>

        </html>
<?php break;
endswitch;
