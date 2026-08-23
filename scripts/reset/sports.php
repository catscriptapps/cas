<?php
// /scripts/reset/sports.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Sport;

function resetSportsTable(): array
{
    $messages = [];
    $tableName = (new Sport())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('sport_id');
            $table->string('sport_name', 100);
            $table->integer('status_id')->default(1);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "created '{$tableName}' table structure.";

        // Auto-increment assigns ids in insertion order -- referenced by
        // leagues.php via the sport names below, not hardcoded ids.
        foreach (['Ice Hockey', 'Ball Hockey'] as $name) {
            Sport::create(['sport_name' => $name, 'status_id' => 1]);
        }

        $messages[] = "seeded 2 sports.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
