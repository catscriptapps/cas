<?php
// /scripts/reset/countries.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Country;

function resetCountriesTable(): array
{
    $messages = [];
    try {
        $tableName = 'countries';

        // 1. Disable constraints to allow a clean drop/recreate
        Capsule::schema()->disableForeignKeyConstraints();
        Capsule::schema()->dropIfExists($tableName);

        // 2. Create structure matching your CREATE TABLE requirements
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id'); // Standard integer primary key
            $table->string('country', 100);
            $table->char('country_code', 3)->nullable();
            $table->string('currency', 255)->nullable();
            $table->string('currency_symbol', 255)->nullable();
        });

        $messages[] = "created modernized 'countries' table structure.";

        // 3. THE LEGACY DATA ARRAY
        // Format: [id, country, country_code, currency, currency_symbol]
        $legacyData = [
            [39, 'Canada', 'CAN', 'CAD', '$'],
            [233, 'United States', 'USA', 'USD', '$'],
        ];

        // 4. Populate from array
        $count = 0;
        foreach ($legacyData as $row) {
            Country::create([
                'id'              => $row[0],
                'country'         => $row[1],
                'country_code'    => $row[2],
                'currency'        => $row[3],
                'currency_symbol' => $row[4],
            ]);
            $count++;
        }

        $messages[] = "successfully imported $count countries.";
    } catch (\Throwable $e) {
        $messages[] = "countries table error: " . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}
