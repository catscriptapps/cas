<?php
// /resources/views/pages/stats/stats-print.php

/**
 * @var string $companyName
 * @var \App\Models\Season $season
 * @var bool $isPlayoff
 * @var array $groupedData
 * @var \Illuminate\Support\Collection $rosters
 */
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

        .group-header {
            font-size: 10pt;
            font-weight: 800;
            color: #ffffff;
            background-color: #33a7a7;
            text-transform: uppercase;
            padding: 6px 10px;
            margin-top: 14px;
        }

        .standings-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            margin-bottom: 16px;
        }

        .standings-table th {
            background-color: #214a4b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #214a4b;
        }

        .standings-table th.team-col {
            text-align: left;
        }

        .standings-table td {
            padding: 7px 6px;
            font-size: 9pt;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .standings-table td.team-col {
            text-align: left;
            font-weight: 700;
            color: #111827;
        }

        .standings-table td.pts-col {
            font-weight: 800;
            color: #33a7a7;
        }

        .diff-pos {
            color: #059669;
            font-weight: 700;
        }

        .diff-neg {
            color: #dc2626;
            font-weight: 700;
        }

        .roster-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .roster-table-title {
            font-size: 9pt;
            font-weight: 800;
            color: #214a4b;
            background-color: #f7f9ee;
            padding: 6px 10px;
            border-left: 3px solid #bad767;
        }

        .roster-table th {
            background-color: #f9fafb;
            color: #374151;
            font-size: 8px;
            text-transform: uppercase;
            padding: 6px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        .roster-table td {
            padding: 6px;
            font-size: 8.5pt;
            border-bottom: 1px solid #f3f4f6;
        }

        .roster-table th.num-col,
        .roster-table td.num-col {
            text-align: center;
        }

        .goalie-divider td {
            background-color: #214a4b;
            color: #ffffff;
            font-weight: 800;
            font-size: 7.5pt;
            text-transform: uppercase;
            padding: 4px 6px;
        }
    </style>
</head>

<body>

    <table class="table-reset header-table">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div class="company-name"><?= htmlspecialchars((string)$companyName) ?></div>
                <div class="text-muted">Official League Standings</div>
            </td>
            <td style="width: 50%;" class="doc-title">
                Standings
            </td>
        </tr>
    </table>

    <div class="season-banner">
        <span class="banner-label"><?= $isPlayoff ? 'Playoffs' : 'Regular Season' ?></span><br>
        <span class="banner-title">
            <?= htmlspecialchars((string)($season->division->division ?? 'Unknown')) ?>
            <span style="color: #6b7280; font-weight: 300;">
                <?= htmlspecialchars((string)($season->season_year ?? '')) ?>
            </span>
        </span>
    </div>

    <?php if (empty($groupedData)): ?>
        <table class="standings-table">
            <tbody>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 30px; color: #9ca3af;">No standings recorded.</td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <?php foreach ($groupedData as $groupName => $teams): ?>
            <div class="group-header">Group <?= htmlspecialchars((string)$groupName) ?></div>
            <table class="standings-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">Rank</th>
                        <th class="team-col" style="width: 26%;">Team</th>
                        <th style="width: 7%;">W</th>
                        <th style="width: 7%;">L</th>
                        <th style="width: 7%;">T</th>
                        <th style="width: 8%;">PTS</th>
                        <th style="width: 8%;">GF</th>
                        <th style="width: 8%;">GA</th>
                        <th style="width: 8%;">Diff</th>
                        <th style="width: 8%;">GP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $rank => $team): ?>
                        <tr>
                            <td><?= $rank + 1 ?></td>
                            <td class="team-col"><?= htmlspecialchars((string)$team['team_name']) ?></td>
                            <td><?= (int)$team['wins'] ?></td>
                            <td><?= (int)$team['losses'] ?></td>
                            <td><?= (int)$team['ties'] ?></td>
                            <td class="pts-col"><?= (int)$team['pts'] ?></td>
                            <td><?= (int)$team['gf'] ?></td>
                            <td><?= (int)$team['ga'] ?></td>
                            <td class="<?= $team['diff'] > 0 ? 'diff-pos' : ($team['diff'] < 0 ? 'diff-neg' : '') ?>">
                                <?= $team['diff'] > 0 ? '+' . $team['diff'] : $team['diff'] ?>
                            </td>
                            <td><?= (int)$team['gp'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!$isPlayoff && $rosters->isNotEmpty()): ?>
        <pagebreak />
        <div class="doc-title" style="text-align: left; font-size: 18pt; margin-bottom: 15px;">Player Statistics</div>

        <?php foreach ($rosters as $team): ?>
            <?php $skaters = $team['skaters'];
            $goalies = $team['goalies']; ?>
            <?php if ($skaters->isEmpty() && $goalies->isEmpty()) continue; ?>

            <div class="roster-table-title"><?= htmlspecialchars((string)$team['team_name']) ?></div>
            <table class="roster-table">
                <thead>
                    <tr>
                        <th style="width: 46%;">Players</th>
                        <th class="num-col" style="width: 13.5%;">GP</th>
                        <th class="num-col" style="width: 13.5%;">G</th>
                        <th class="num-col" style="width: 13.5%;">A</th>
                        <th class="num-col" style="width: 13.5%;">PTS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($skaters as $player):
                        $stat = $player->stats->first();
                        $gp = $stat->games_played ?? 0;
                        $g = $stat->goals ?? 0;
                        $a = $stat->assists ?? 0;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars((string)($player->profile->full_name ?? 'Unknown Player')) ?></td>
                            <td class="num-col"><?= $gp ?: '' ?></td>
                            <td class="num-col"><?= $g ?: '' ?></td>
                            <td class="num-col"><?= $a ?: '' ?></td>
                            <td class="num-col"><?= ($g + $a) ?: '' ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($goalies->isNotEmpty()): ?>
                        <tr class="goalie-divider">
                            <td colspan="5">Goalies</td>
                        </tr>
                        <?php foreach ($goalies as $player):
                            $stat = $player->stats->first();
                            $gp = $stat->games_played ?? 0;
                            $ga = $stat->goals_against ?? 0;
                            $sog = $stat->shots_on_goal ?? 0;
                            $gaa = $gp > 0 ? round($ga / $gp, 2) : 0;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($player->profile->full_name ?? 'Unknown Player')) ?></td>
                                <td class="num-col"><?= $gp ?: '' ?></td>
                                <td class="num-col"><?= $ga ?: '' ?></td>
                                <td class="num-col"><?= $gaa > 0 ? number_format((float)$gaa, 2) : '' ?></td>
                                <td class="num-col"><?= $sog ?: '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="text-align: right; font-size: 8pt; color: #9ca3af; font-weight: bold; margin-top: 20px;">
        Generated on <?= date('F j, Y') ?> &bull; <?= htmlspecialchars((string)$companyName) ?>
    </div>

</body>

</html>
