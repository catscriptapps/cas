<?php
// /scripts/reset/players.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Player;

/**
 * Seeds the real 956 roster slots from legacy cas-sports' 3 active seasons
 * (see scripts/reset/data/players.php, a generated fixture). player_id
 * preserved exactly; user_id points at the real registrant imported by
 * scripts/reset/registrations.php's importLegacyScheduleRegistrants().
 */
function resetPlayersTable(): array
{
    $messages = [];
    $tableName = (new Player())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('player_id');
            $table->unsignedInteger('season_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('player_number', 11)->nullable();
            $table->tinyInteger('is_goalie')->default(0);
            $table->integer('status_id')->default(1);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "fresh {$tableName} table created.";

        $players = require __DIR__ . '/data/players.php';
        foreach (array_chunk($players, 200) as $chunk) {
            Capsule::table($tableName)->insert($chunk);
        }
        $messages[] = 'seeded ' . count($players) . ' players from legacy cas-sports.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
