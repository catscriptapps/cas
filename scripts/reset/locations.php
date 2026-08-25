<?php
// /scripts/reset/locations.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Location;

/**
 * Rink/venue list for the schedule form's Location dropdown -- exact legacy
 * cas-sports IDs and labels preserved (note the gaps: 7-12 were never used
 * in legacy either).
 */
function resetLocationsTable(): array
{
    $messages = [];
    $tableName = (new Location())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('location_id');
            $table->string('location_desc', 300)->nullable();
        });
        $messages[] = "fresh {$tableName} table created.";

        $locations = [
            1 => 'Alliston',
            2 => 'Angus',
            3 => 'Barrie',
            4 => 'Thornton Ball',
            5 => 'IRC - GOLD',
            6 => 'IRC - RED',
            13 => 'Alliston - Playoffs',
            14 => 'Angus - Playoffs',
            15 => 'Barrie - Playoffs',
            16 => 'Thornton Ball - Playoffs',
            17 => 'IRC - GOLD - Playoffs',
            18 => 'IRC - RED - Playoffs',
            19 => 'Thornton Ice',
            20 => 'Thornton Ice - Playoffs',
        ];

        foreach ($locations as $id => $desc) {
            Capsule::table($tableName)->insert(['location_id' => $id, 'location_desc' => $desc]);
        }

        $messages[] = 'seeded ' . count($locations) . ' locations.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
