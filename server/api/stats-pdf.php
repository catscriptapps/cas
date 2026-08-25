<?php
// /server/api/stats-pdf.php

declare(strict_types=1);

use App\Models\Season;
use App\Utils\IdEncoder;
use Mpdf\Mpdf;
use Src\Controller\StatsController;

$encodedSeasonId = $_GET['season_id'] ?? null;
$isPlayoff = (isset($_GET['is_playoff']) && $_GET['is_playoff'] == '1');

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

    $controller = new StatsController();
    $statsData = $controller->getStatsData($seasonId, $isPlayoff);

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

    $printPath = __DIR__ . '/../../resources/views/pages/stats/stats-print.php';

    $html = renderView($printPath, [
        'season' => $season,
        'isPlayoff' => $isPlayoff,
        'groupedData' => $statsData['groupedData'],
        'rosters' => $statsData['rosters'],
        'companyName' => $companyName,
    ]);

    $mpdf->WriteHTML($html);

    $titlePrefix = $season->division->division ?? 'Stats';
    $safeTitle = str_replace(' ', '-', $titlePrefix);
    $modePrefix = $isPlayoff ? 'Playoff' : 'Regular-Season';

    $mpdf->Output("{$modePrefix}-Stats-{$safeTitle}.pdf", \Mpdf\Output\Destination::INLINE);
} catch (\Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'messages' => [$e->getMessage()]]);
}
