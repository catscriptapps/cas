<?php
// /scripts/reset/stats.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Stat;

/**
 * Team win/loss/tie/goals standings -- one row per (team_id, season_id,
 * is_playoff), maintained entirely by hand (see Stat model docblock for
 * why -- there's no game-outcome inference anywhere in this feature).
 * Seeds the real 126 rows from legacy cas-sports' 3 active seasons (see
 * scripts/reset/data/stats.php, a generated fixture). entry_id and team_id
 * preserved exactly against scripts/reset/teams.php.
 */
function resetStatsTable(): array
{
    $messages = [];
    $tableName = (new Stat())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->unsignedInteger('season_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('ties')->default(0);
            $table->integer('goals_for')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('is_playoff')->nullable();
            $table->datetime('timestamp')->nullable();

            $table->index(['season_id', 'team_id', 'is_playoff']);
        });
        $messages[] = "fresh {$tableName} table created.";

        $stats = require __DIR__ . '/data/stats.php';
        Capsule::table($tableName)->insert($stats);
        $messages[] = 'seeded ' . count($stats) . ' team standings rows from legacy cas-sports.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
