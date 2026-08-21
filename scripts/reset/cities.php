<?php
// /scripts/reset/cities.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\City;

function resetCitiesTable(): array
{
    $messages = [];
    try {
        $tableName = 'cities';

        // 1. Disable constraints to allow a clean drop/recreate
        Capsule::schema()->disableForeignKeyConstraints();
        Capsule::schema()->dropIfExists($tableName);

        // 2. Create structure matching system requirements
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->mediumIncrements('id'); // mediumint(8) unsigned primary key
            $table->string('city', 100);
            $table->mediumInteger('region_id')->unsigned()->default(866);
        });

        $messages[] = "created modernized 'cities' table structure.";

        // 3. THE LEGACY DATA ARRAY
        // Format: [id, city, region_id]
        $legacyData = [
            [1, 'Barrie', 866],
            [2, 'Orillia', 866],
        ];

        // 4. Populate from array
        $count = 0;
        foreach ($legacyData as $row) {
            City::create([
                'id'        => $row[0],
                'city'      => $row[1],
                'region_id' => $row[2],
            ]);
            $count++;
        }

        $messages[] = "successfully imported $count cities.";
    } catch (\Throwable $e) {
        $messages[] = "cities table error: " . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}
