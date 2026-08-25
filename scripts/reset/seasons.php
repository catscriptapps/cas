<?php
// /scripts/reset/seasons.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Season;

/**
 * A Season is one division's roster/schedule cycle for a given year. Seeded
 * with the exact 3 active legacy cas-sports seasons (season_id preserved
 * exactly, since scripts/reset/data/teams.php, players.php, and
 * schedules.php all reference these same 3 IDs directly). Each legacy
 * season's division was matched by name to its equivalent in this project's
 * own League Management divisions (built independently in an earlier phase,
 * but seeded from the same real league data, so the names line up exactly):
 *   legacy division 151 "Mens Ice (Summer)"      -> divisions.division_id 5
 *   legacy division 153 "Mens 35+ Ice (Summer)"  -> divisions.division_id 7
 *   legacy division 152 "Women's Ice (Summer)"   -> divisions.division_id 6
 */
function resetSeasonsTable(): array
{
    $messages = [];
    $tableName = (new Season())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('season_id');
            $table->unsignedInteger('division_id')->nullable();
            $table->string('season_year', 4)->nullable();
            $table->integer('status_id')->default(1);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "fresh {$tableName} table created.";

        $seasons = [
            ['season_id' => 111, 'division_id' => 5, 'season_year' => '2025', 'status_id' => 1, 'date_created' => '2025-09-01', 'timestamp' => '2025-09-01 14:27:01'],
            ['season_id' => 112, 'division_id' => 7, 'season_year' => '2025', 'status_id' => 1, 'date_created' => '2025-09-01', 'timestamp' => '2025-09-01 14:27:06'],
            ['season_id' => 113, 'division_id' => 6, 'season_year' => '2025', 'status_id' => 1, 'date_created' => '2025-09-01', 'timestamp' => '2025-09-01 14:27:09'],
        ];

        Capsule::table($tableName)->insert($seasons);
        $messages[] = 'seeded ' . count($seasons) . ' seasons.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
