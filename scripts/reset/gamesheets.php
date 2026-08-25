<?php
// /scripts/reset/gamesheets.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Gamesheet;

/**
 * Per-player, per-game stat sheet -- one row per (schedule_id, player_id),
 * maintained by hand from the Gamesheets detail page's inline auto-save.
 * Seeds the real 17,416 rows from legacy cas-sports' 3 active seasons (see
 * scripts/reset/data/gamesheets.php, a generated fixture). entry_id,
 * schedule_id, and player_id preserved exactly against scripts/reset/
 * schedules.php and scripts/reset/players.php.
 */
function resetGamesheetsTable(): array
{
    $messages = [];
    $tableName = (new Gamesheet())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->unsignedInteger('schedule_id')->nullable();
            $table->unsignedInteger('season_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->unsignedInteger('player_id')->nullable();
            $table->integer('period')->nullable();
            $table->string('time_of_goal', 11)->nullable();
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('games_played')->default(0);
            $table->datetime('timestamp')->nullable();

            $table->index(['schedule_id', 'player_id']);
        });
        $messages[] = "fresh {$tableName} table created.";

        $gamesheets = require __DIR__ . '/data/gamesheets.php';
        foreach (array_chunk($gamesheets, 500) as $chunk) {
            Capsule::table($tableName)->insert($chunk);
        }
        $messages[] = 'seeded ' . count($gamesheets) . ' gamesheet stat rows from legacy cas-sports.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
