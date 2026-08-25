<?php
// /resources/views/pages/stats/detail.php

declare(strict_types=1);

use Src\Controller\StatsController;

$encodedId = $GLOBALS['encodedId'] ?? '';

$statsController = new StatsController();
$response = $statsController->index(['encoded_id' => $encodedId]);

if (!$response['success']) {
    echo "<div class='p-12 text-center'>
            <h1 class='text-2xl font-black text-gray-900 dark:text-white font-sans uppercase'>" . htmlspecialchars((string)($response['messages'][0] ?? 'Stats Not Found')) . "</h1>
            <a href='{$baseUrl}stats' data-partial class='text-primary-600 font-bold hover:underline mt-4 inline-block'>Back to Stats+Standings</a>
          </div>";
    return;
}

$data = $response['data'] ?? [];
$divisionName = $data['divisionName'] ?? 'Unknown';
$seasonYear = $data['seasonYear'] ?? '';
$seasonId = (int)($data['season_id'] ?? 0);
$rosters = $data['rosters'] ?? collect();
?>

<div class="space-y-8 py-10 max-w-full" data-season-encoded-id="<?php echo htmlspecialchars((string)$encodedId); ?>" data-season-id="<?= $seasonId ?>">

    <?php include __DIR__ . '/../../components/stats/header.php'; ?>

    <div id="pane-regular" class="stats-pane space-y-8">
        <?php
        $groupedData = $data['groupedStats'] ?? [];
        $isPlayoff = false;
        include __DIR__ . '/../../components/stats/standings-table.php';
        ?>

        <?php include __DIR__ . '/../../components/stats/roster-accordion.php'; ?>
    </div>

    <div id="pane-playoffs" class="stats-pane space-y-8 hidden">
        <?php
        $groupedData = $data['playoffGroupedStats'] ?? [];
        $isPlayoff = true;
        include __DIR__ . '/../../components/stats/standings-table.php';
        ?>
    </div>

</div>
