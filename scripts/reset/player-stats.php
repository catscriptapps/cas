<?php
// /scripts/reset/player-stats.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PlayerStat;

/**
 * Player season goal/assist/point totals (and goalie GA/SOG) -- one row per
 * (player_id, season_id), maintained by hand (see PlayerStat model
 * docblock). Seeds the real 956 rows from legacy cas-sports' 3 active
 * seasons (see scripts/reset/data/player-stats.php, a generated fixture).
 * entry_id and player_id preserved exactly against scripts/reset/players.php.
 */
function resetPlayerStatsTable(): array
{
    $messages = [];
    $tableName = (new PlayerStat())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->unsignedInteger('season_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->unsignedInteger('player_id')->nullable();
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('points')->default(0);
            $table->integer('games_played')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('shots_on_goal')->default(0);
            $table->datetime('timestamp')->nullable();

            $table->index(['season_id', 'player_id']);
        });
        $messages[] = "fresh {$tableName} table created.";

        $playerStats = require __DIR__ . '/data/player-stats.php';
        foreach (array_chunk($playerStats, 200) as $chunk) {
            Capsule::table($tableName)->insert($chunk);
        }
        $messages[] = 'seeded ' . count($playerStats) . ' player stat rows from legacy cas-sports.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
