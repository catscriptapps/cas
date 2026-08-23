<?php
// /scripts/reset/leagues.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\League;
use App\Models\Sport;

function resetLeaguesTable(): array
{
    $messages = [];
    $tableName = (new League())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('league_id');
            $table->unsignedInteger('sport_id');
            $table->string('league', 255);
            $table->tinyInteger('is_ball')->default(0);
            $table->integer('status_id')->default(1);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();

            $table->index('sport_id', 'sport_league');
            $table->foreign('sport_id', 'sport_league_final')
                ->references('sport_id')
                ->on('sports')
                ->onDelete('cascade');
        });
        $messages[] = "created '{$tableName}' table structure.";

        $iceHockeyId = Sport::where('sport_name', 'Ice Hockey')->value('sport_id');
        $ballHockeyId = Sport::where('sport_name', 'Ball Hockey')->value('sport_id');

        // Real league names/structure drawn from the live cas_sports_db
        // (essahockey.com), trimmed of test/orphaned rows.
        $data = [
            ['sport_id' => $iceHockeyId, 'league' => 'Winter (October - March)', 'is_ball' => 0],
            ['sport_id' => $iceHockeyId, 'league' => 'Summer (April - September)', 'is_ball' => 0],
            ['sport_id' => $ballHockeyId, 'league' => 'Thornton Outdoor Arena', 'is_ball' => 1],
            ['sport_id' => $ballHockeyId, 'league' => 'Angus Arena', 'is_ball' => 1],
            ['sport_id' => $ballHockeyId, 'league' => 'Midhurst Arena', 'is_ball' => 1],
        ];

        foreach ($data as $item) {
            League::create($item + ['status_id' => 1, 'date_created' => date('Y-m-d')]);
        }

        $messages[] = "seeded " . count($data) . " leagues.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
