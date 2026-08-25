<?php
// /server/api/gamesheets-pdf.php

declare(strict_types=1);

use App\Models\Schedule;
use App\Models\Season;
use App\Utils\IdEncoder;
use Mpdf\Mpdf;
use Src\Controller\GamesheetsController;
use Src\Controller\TeamsController;

$encodedSeasonId = $_GET['season_id'] ?? null;
$encodedScheduleId = $_GET['schedule_id'] ?? null;

try {
    if (!$encodedSeasonId) throw new \Exception('Missing Season ID.');

    $seasonId = (int)IdEncoder::decode($encodedSeasonId);
    $season = Season::with('division')->find($seasonId);
    if (!$season) throw new \Exception('Season not found.');

    $companyName = $_ENV['APP_NAME'] ?? 'Canadian All Star Sports';

    $controller = new GamesheetsController();

    $query = Schedule::with(['homeTeam', 'awayTeam', 'locationRelation', 'season.division'])
        ->where('status_id', Schedule::STATUS_ACTIVE)
        ->where('season_id', $seasonId)
        ->orderBy('game_date', 'asc')
        ->orderByRaw("STR_TO_DATE(game_time, '%l:%i %p') ASC");

    if ($encodedScheduleId) {
        $scheduleId = is_numeric($encodedScheduleId) ? (int)$encodedScheduleId : (int)IdEncoder::decode((string)$encodedScheduleId);
        $query->where('entry_id', $scheduleId);
    }

    $games = $query->get();

    $teamsData = TeamsController::getBySeason($seasonId)->map(fn($team) => [
        'team_name' => $team->team_name,
        'group_name' => $team->group->group_name ?? $team->team_group,
        'rep_name' => $team->representative->full_name ?? 'N/A',
        'player_count' => $team->players_count ?? 0,
    ])->toArray();

    $gameRosters = [];
    foreach ($games as $game) {
        $gameRosters[$game->entry_id] = $controller->getRawRosterData($game);
    }

    $mpdfTemp = __DIR__ . '/../storage/mpdf-temp';
    if (!is_dir($mpdfTemp)) mkdir($mpdfTemp, 0777, true);

    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'tempDir' => $mpdfTemp,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'fontDir' => array_merge($defaultConfig['fontDir'], [__DIR__ . '/../storage/fonts']),
        'fontdata' => $defaultFontConfig['fontdata'] + [
            'quicksand' => [
                'R' => 'Quicksand-Regular.ttf',
                'B' => 'Quicksand-Bold.ttf',
            ],
        ],
        'default_font' => 'quicksand',
    ]);

    $printPath = __DIR__ . '/../../resources/views/pages/gamesheets/gamesheet-print.php';

    // Written in separate WriteHTML() calls (header, then one per game, then
    // footer) rather than one giant string -- a full season's worth of
    // per-game roster tables can exceed mPDF's internal HTML-parser regex
    // backtrack limit if handed over in a single call.
    $mpdf->WriteHTML(renderView($printPath, [
        'mode' => 'header',
        'companyName' => $companyName,
        'season' => $season,
        'teams' => $teamsData,
    ]));

    if ($games->isEmpty()) {
        $mpdf->WriteHTML('<div style="text-align: center; padding: 40px; color: #9ca3af;">No games found.</div>');
    } else {
        foreach ($games as $game) {
            $mpdf->WriteHTML(renderView($printPath, [
                'mode' => 'game',
                'game' => $game,
                'rosterData' => $gameRosters[$game->entry_id] ?? ['home' => ['name' => 'Home', 'players' => collect()], 'away' => ['name' => 'Away', 'players' => collect()]],
            ]));
        }
    }

    $mpdf->WriteHTML(renderView($printPath, [
        'mode' => 'footer',
        'companyName' => $companyName,
    ]));

    if ($encodedScheduleId && $games->count() > 0) {
        $filename = "Gamesheet-{$games[0]->homeTeam->team_name}-vs-{$games[0]->awayTeam->team_name}.pdf";
    } else {
        $filename = "Gamesheets-{$season->division->division}.pdf";
    }
    $filename = str_replace(' ', '-', $filename);

    $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
} catch (\Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'messages' => [$e->getMessage()]]);
}
