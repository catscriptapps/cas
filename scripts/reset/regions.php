<?php
// /scripts/reset/regions.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Region;

function resetRegionsTable(): array
{
    $messages = [];
    try {
        $tableName = 'regions';

        // 1. Disable constraints to drop cleanly
        Capsule::schema()->disableForeignKeyConstraints();
        Capsule::schema()->dropIfExists($tableName);

        // 2. Create structure matching your SQL
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id'); // Standard integer primary key
            $table->string('region', 255);
            $table->unsignedInteger('country_id');

            // Index and Foreign Key
            $table->index('country_id', 'country_region');
            $table->foreign('country_id', 'country_region_final')
                ->references('id')
                ->on('countries')
                ->onDelete('cascade');
        });

        $messages[] = "created modernized 'regions' table structure.";

        // 3. LEGACY DATA ARRAY
        // Format: [id, region, country_id]
        $legacyData = [

            [866, 'Ontario', 39],
            [867, 'Manitoba', 39],
            [868, 'New Brunswick', 39],
            [869, 'Yukon', 39],
            [870, 'Saskatchewan', 39],
            [871, 'Prince Edward Island', 39],
            [872, 'Alberta', 39],
            [873, 'Quebec', 39],
            [874, 'Nova Scotia', 39],
            [875, 'British Columbia', 39],
            [876, 'Nunavut', 39],
            [877, 'Newfoundland and Labrador', 39],
            [878, 'Northwest Territories', 39],
            [1398, 'Howland Island', 233],
            [1399, 'Delaware', 233],
            [1400, 'Alaska', 233],
            [1401, 'Maryland', 233],
            [1402, 'Baker Island', 233],
            [1403, 'Kingman Reef', 233],
            [1404, 'New Hampshire', 233],
            [1405, 'Wake Island', 233],
            [1406, 'Kansas', 233],
            [1407, 'Texas', 233],
            [1408, 'Nebraska', 233],
            [1409, 'Vermont', 233],
            [1410, 'Jarvis Island', 233],
            [1411, 'Hawaii', 233],
            [1412, 'Guam', 233],
            [1413, 'United States Virgin Islands', 233],
            [1414, 'Utah', 233],
            [1415, 'Oregon', 233],
            [1416, 'California', 233],
            [1417, 'New Jersey', 233],
            [1418, 'North Dakota', 233],
            [1419, 'Kentucky', 233],
            [1420, 'Minnesota', 233],
            [1421, 'Oklahoma', 233],
            [1422, 'Pennsylvania', 233],
            [1423, 'New Mexico', 233],
            [1424, 'American Samoa', 233],
            [1425, 'Illinois', 233],
            [1426, 'Michigan', 233],
            [1427, 'Virginia', 233],
            [1428, 'Johnston Atoll', 233],
            [1429, 'West Virginia', 233],
            [1430, 'Mississippi', 233],
            [1431, 'Northern Mariana Islands', 233],
            [1432, 'United States Minor Outlying Islands', 233],
            [1433, 'Massachusetts', 233],
            [1434, 'Arizona', 233],
            [1435, 'Connecticut', 233],
            [1436, 'Florida', 233],
            [1437, 'District of Columbia', 233],
            [1438, 'Midway Atoll', 233],
            [1439, 'Navassa Island', 233],
            [1440, 'Indiana', 233],
            [1441, 'Wisconsin', 233],
            [1442, 'Wyoming', 233],
            [1443, 'South Carolina', 233],
            [1444, 'Arkansas', 233],
            [1445, 'South Dakota', 233],
            [1446, 'Montana', 233],
            [1447, 'North Carolina', 233],
            [1448, 'Palmyra Atoll', 233],
            [1449, 'Puerto Rico', 233],
            [1450, 'Colorado', 233],
            [1451, 'Missouri', 233],
            [1452, 'New York', 233],
            [1453, 'Maine', 233],
            [1454, 'Tennessee', 233],
            [1455, 'Georgia', 233],
            [1456, 'Alabama', 233],
            [1457, 'Louisiana', 233],
            [1458, 'Nevada', 233],
            [1459, 'Iowa', 233],
            [1460, 'Idaho', 233],
            [1461, 'Rhode Island', 233],
            [1462, 'Washington', 233],
        ];

        // 4. Populate from array
        $count = 0;
        foreach ($legacyData as $row) {
            Region::create([
                'id'         => $row[0],
                'region'     => $row[1],
                'country_id' => $row[2],
            ]);
            $count++;
        }

        $messages[] = "successfully imported $count regions.";
    } catch (\Throwable $e) {
        $messages[] = "regions table error: " . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}
