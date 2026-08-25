<?php
// /server/api/schedules-pdf.php

declare(strict_types=1);

use App\Models\Season;
use App\Models\Schedule;
use App\Utils\IdEncoder;
use Mpdf\Mpdf;
use Src\Controller\SchedulesController;

$encodedSeasonId = $_GET['season_id'] ?? null;
$viewAll = (isset($_GET['view_all']) && $_GET['view_all'] == '1');

try {
    if (!$encodedSeasonId) {
        throw new \Exception('Missing Season ID.');
    }

    $seasonId = (int)IdEncoder::decode($encodedSeasonId);
    $season = Season::with('division')->find($seasonId);

    if (!$season) {
        throw new \Exception('Season not found.');
    }

    $companyName = $_ENV['APP_NAME'] ?? 'Canadian All Star Sports';

    $controller = new SchedulesController();

    if ($viewAll) {
        $activeSeasonIds = Season::where('status_id', Season::STATUS_ACTIVE)->pluck('season_id')->toArray();
        $games = Schedule::with(['homeTeam', 'awayTeam', 'season.division', 'locationRelation'])
            ->whereIn('season_id', $activeSeasonIds)
            ->where('status_id', Schedule::STATUS_ACTIVE)
            ->orderBy('game_date', 'asc')
            ->orderByRaw("STR_TO_DATE(game_time, '%l:%i %p') ASC")
            ->get();
        $teams = [];
    } else {
        $result = $controller->getScheduleDetail($encodedSeasonId);
        if (!$result['success']) throw new \Exception($result['messages'][0]);

        $games = $result['data']['games'];
        $teams = $result['data']['teams'] ?? [];
    }

    $mpdfTemp = __DIR__ . '/../storage/mpdf-temp';
    if (!is_dir($mpdfTemp)) mkdir($mpdfTemp, 0777, true);

    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'tempDir' => $mpdfTemp,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'fontDir' => array_merge($defaultConfig['fontDir'], [__DIR__ . '/../storage/fonts']),
        'fontdata' => $defaultFontConfig['fontdata'] + [
            'quicksand' => [
                'R' => 'Quicksand-Regular.ttf',
                'B' => 'Quicksand-Bold.ttf',
            ],
        ],
        'default_font' => 'quicksand',
    ]);

    $printPath = __DIR__ . '/../../resources/views/pages/schedules/schedule-print.php';

    $html = renderView($printPath, [
        'season' => $season,
        'games' => $games,
        'teams' => $teams,
        'companyName' => $companyName,
        'isViewAll' => $viewAll,
    ]);

    $mpdf->WriteHTML($html);

    $titlePrefix = $viewAll ? 'Master-Schedule' : ($season->division->division ?? 'Schedule');
    $safeTitle = str_replace(' ', '-', $titlePrefix);

    $mpdf->Output("Schedule-{$safeTitle}.pdf", \Mpdf\Output\Destination::INLINE);
} catch (\Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'messages' => [$e->getMessage()]]);
}
