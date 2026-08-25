<?php
// /scripts/reset/schedules.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Schedule;

/**
 * Seeds the real 595 games from legacy cas-sports' 3 active seasons (see
 * scripts/reset/data/schedules.php, a generated fixture). entry_id, home,
 * and away preserved exactly against scripts/reset/teams.php's team_id
 * values -- no ID remapping needed anywhere in this import chain.
 *
 * `game_time` is intentionally a free-text string ("7:15 PM"), matching
 * legacy exactly -- see Schedule model docblock.
 */
function resetSchedulesTable(): array
{
    $messages = [];
    $tableName = (new Schedule())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->unsignedInteger('season_id')->nullable();
            $table->date('game_date')->nullable();
            $table->string('game_time', 20)->nullable();
            $table->unsignedInteger('location')->nullable();
            $table->unsignedInteger('home')->nullable();
            $table->unsignedInteger('away')->nullable();
            $table->string('referee1', 100)->nullable();
            $table->string('referee2', 100)->nullable();
            $table->string('timekeep', 100)->nullable();
            $table->tinyInteger('is_playoff')->default(0);
            $table->integer('status_id')->nullable();
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();

            $table->index('season_id');
            $table->index('game_date');
        });
        $messages[] = "fresh {$tableName} table created.";

        $schedules = require __DIR__ . '/data/schedules.php';
        foreach (array_chunk($schedules, 200) as $chunk) {
            Capsule::table($tableName)->insert($chunk);
        }
        $messages[] = 'seeded ' . count($schedules) . ' schedules from legacy cas-sports.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
