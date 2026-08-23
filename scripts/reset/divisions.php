<?php
// /scripts/reset/divisions.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Division;
use App\Models\League;

function resetDivisionsTable(): array
{
    $messages = [];
    $tableName = (new Division())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('division_id');
            $table->unsignedInteger('league_id');
            $table->string('division', 255);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('status_id')->default(1);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();

            $table->index('league_id', 'league_division');
            $table->foreign('league_id', 'league_division_final')
                ->references('league_id')
                ->on('leagues')
                ->onDelete('cascade');
        });
        $messages[] = "created '{$tableName}' table structure.";

        $winterId = League::where('league', 'Winter (October - March)')->value('league_id');
        $summerId = League::where('league', 'Summer (April - September)')->value('league_id');
        $thorntonId = League::where('league', 'Thornton Outdoor Arena')->value('league_id');
        $angusId = League::where('league', 'Angus Arena')->value('league_id');
        $midhurstId = League::where('league', 'Midhurst Arena')->value('league_id');

        // Real division names/prices drawn from the live cas_sports_db
        // (essahockey.com), trimmed of test/orphaned rows.
        $data = [
            ['league_id' => $winterId, 'division' => 'Mens Ice', 'price' => 400.00],
            ['league_id' => $winterId, 'division' => 'Womens Ice', 'price' => 400.00],
            ['league_id' => $winterId, 'division' => 'Mens 35+ Ice', 'price' => 400.00],
            ['league_id' => $winterId, 'division' => 'Mens 50+ Ice', 'price' => 400.00],

            ['league_id' => $summerId, 'division' => 'Mens Ice', 'price' => 465.00],
            ['league_id' => $summerId, 'division' => 'Womens Ice', 'price' => 465.00],
            ['league_id' => $summerId, 'division' => 'Mens 35+ Ice', 'price' => 465.00],

            ['league_id' => $thorntonId, 'division' => 'PUC Spring', 'price' => 100.00],
            ['league_id' => $thorntonId, 'division' => 'Womens Spring Outdoor', 'price' => 210.00],
            ['league_id' => $thorntonId, 'division' => 'Mens Spring Outdoor', 'price' => 210.00],
            ['league_id' => $thorntonId, 'division' => 'Adult Co-Ed Spring Outdoor', 'price' => 210.00],
            ['league_id' => $thorntonId, 'division' => 'Kids 4 to 6 years old', 'price' => 210.00],
            ['league_id' => $thorntonId, 'division' => 'Kids 7 to 9 years old', 'price' => 210.00],
            ['league_id' => $thorntonId, 'division' => 'Kids 10 to 13 years old', 'price' => 210.00],

            ['league_id' => $angusId, 'division' => "Women's", 'price' => 225.00],
            ['league_id' => $angusId, 'division' => "Men's", 'price' => 225.00],
            ['league_id' => $angusId, 'division' => 'Co-Ed', 'price' => 225.00],
            ['league_id' => $angusId, 'division' => 'IP (3 to 6 yrs)', 'price' => 225.00],
            ['league_id' => $angusId, 'division' => 'U10 (7 to 9 yrs)', 'price' => 225.00],
            ['league_id' => $angusId, 'division' => 'U14 (10 to 13 yrs)', 'price' => 225.00],
            ['league_id' => $angusId, 'division' => 'U17 (14 to 17 yrs)', 'price' => 225.00],

            ['league_id' => $midhurstId, 'division' => 'Womens', 'price' => 225.00],
        ];

        foreach ($data as $item) {
            Division::create($item + ['status_id' => 1, 'date_created' => date('Y-m-d')]);
        }

        $messages[] = "seeded " . count($data) . " divisions.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
