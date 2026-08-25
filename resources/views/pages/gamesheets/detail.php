<?php
// /resources/views/pages/gamesheets/detail.php

declare(strict_types=1);

use Src\Controller\GamesheetsController;

$encodedId = $GLOBALS['encodedId'] ?? '';

$gamesheetsController = new GamesheetsController();
$response = $gamesheetsController->index(['action' => 'detail', 'encoded_id' => $encodedId]);

if (!$response['success']) {
    echo "<div class='p-12 text-center'>
            <h1 class='text-2xl font-black text-gray-900 dark:text-white font-sans uppercase'>" . htmlspecialchars((string)($response['messages'][0] ?? 'Gamesheet Not Found')) . "</h1>
            <a href='{$baseUrl}gamesheets' data-partial class='text-primary-600 font-bold hover:underline mt-4 inline-block'>Back to Gamesheets</a>
          </div>";
    return;
}

$data = $response['data'] ?? [];
$divisionName = $data['divisionName'] ?? 'Unknown';
$seasonYear = $data['seasonYear'] ?? '';

$teams = $data['teams'] ?? [];
$games = $data['games'] ?? collect();
?>

<div class="space-y-8 py-10 max-w-full" data-season-encoded-id="<?php echo htmlspecialchars((string)$encodedId); ?>">

    <?php include __DIR__ . '/../../components/gamesheets/header.php'; ?>

    <div id="schedule-content-area" class="space-y-8">

        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm w-full overflow-clip">
            <?php include __DIR__ . '/../../components/schedules/teams-table.php'; ?>
        </div>

        <?php include __DIR__ . '/../../components/gamesheets/games-table.php'; ?>

    </div>
</div>
