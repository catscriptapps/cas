<?php
// /scripts/reset/teams.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Team;

/**
 * Seeds the real 63 teams from legacy cas-sports' 3 active seasons (596
 * schedules / 65 teams / 960 players total in legacy across all-time; this
 * project imports only the 3 currently-active seasons' worth -- see
 * scripts/reset/data/teams.php, a generated fixture, not hand-written).
 * team_id preserved exactly so scripts/reset/players.php and
 * scripts/reset/schedules.php (home/away) can reference these rows directly
 * with no ID remapping.
 */
function resetTeamsTable(): array
{
    $messages = [];
    $tableName = (new Team())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('team_id');
            $table->unsignedInteger('season_id')->nullable();
            $table->string('team_number', 50)->nullable();
            $table->string('team_name', 255)->nullable();
            $table->unsignedInteger('team_rep_id')->nullable();
            $table->text('contact')->nullable();
            $table->text('team_coach')->nullable();
            $table->integer('status_id')->default(1);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
            $table->string('team_group', 50)->nullable();
        });
        $messages[] = "fresh {$tableName} table created.";

        $teams = require __DIR__ . '/data/teams.php';
        Capsule::table($tableName)->insert($teams);
        $messages[] = 'seeded ' . count($teams) . ' teams from legacy cas-sports.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
