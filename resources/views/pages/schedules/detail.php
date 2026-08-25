<?php
// /resources/views/pages/schedules/detail.php

declare(strict_types=1);

use Src\Controller\SchedulesController;

$encodedId = $GLOBALS['encodedId'] ?? '';

$scheduleController = new SchedulesController();
$response = $scheduleController->getScheduleDetail($encodedId);

if (!$response['success']) {
    echo "<div class='p-12 text-center'>
            <h1 class='text-2xl font-black text-gray-900 dark:text-white font-sans uppercase'>" . htmlspecialchars((string)($response['messages'][0] ?? 'Schedule Not Found')) . "</h1>
            <a href='{$baseUrl}schedules' data-partial class='text-primary-600 font-bold hover:underline mt-4 inline-block'>Back to Schedules</a>
          </div>";
    return;
}

$data = $response['data'] ?? [];
$divisionName = $data['divisionName'] ?? 'Unknown';
$seasonYear = $data['seasonYear'] ?? '';

$teams = $data['teams'] ?? [];
$games = $data['games'] ?? collect();
?>

<?php
// No `overflow-x-hidden` (or any overflow-x) here: setting it forces
// overflow-y to compute as `auto`, which makes this div the sticky
// positioning container for its descendants instead of the viewport --
// breaking the sticky page header and sticky table theads below (same
// CSS gotcha already worked around on the Registrations table).
?>
<div class="space-y-8 py-10 max-w-full" data-season-encoded-id="<?php echo htmlspecialchars((string)$encodedId); ?>">

    <?php include __DIR__ . '/../../components/schedules/header.php'; ?>

    <div id="schedule-content-area" class="space-y-8">

        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm w-full overflow-hidden">
            <?php include __DIR__ . '/../../components/schedules/teams-table.php'; ?>
        </div>

        <?php include __DIR__ . '/../../components/schedules/games-table.php'; ?>

    </div>
</div>
