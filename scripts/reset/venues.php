<?php
// /scripts/reset/venues.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Venue;

/**
 * Seeds the 5 real venues that used to be hardcoded directly in
 * resources/views/pages/locations.php -- this table (plus that page's new
 * admin add/edit/reorder/delete controls) is what makes them manageable
 * without a code deploy. Images already live in public/images/locations/
 * (copied from legacy essahockey_live/images/locations/ in an earlier pass).
 */
function resetVenuesTable(): array
{
    $messages = [];
    $tableName = (new Venue())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('venue_id');
            $table->string('name', 255);
            $table->string('address', 300)->nullable();
            $table->text('directions')->nullable();
            $table->string('sport', 10)->default(Venue::SPORT_BALL)->index();
            $table->string('image', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "created '{$tableName}' table structure.";

        $venues = [
            [
                'name' => 'Thornton Outdoor Rink',
                'address' => '242 Barrie St, Thornton, ON',
                'directions' => null,
                'sport' => Venue::SPORT_BALL,
                'image' => 'images/locations/thornton_arena.png',
            ],
            [
                'name' => 'Angus Outdoor Rink',
                'address' => 'Off the 5th Line, Angus, ON',
                'directions' => '152 Greenwood Drive, Angus (5th Line to Gold Park Gate to Greenwood)',
                'sport' => Venue::SPORT_BALL,
                'image' => 'images/locations/angus_rink.png',
            ],
            [
                'name' => 'Alliston Memorial Arena',
                'address' => '49 Nelson St, Alliston, ON',
                'directions' => 'From Thornton/Cookstown -- Highway 89 to Church St to Nelson. From Angus/Baxter -- County Rd 10 to Highway 89 to Church St to Nelson.',
                'sport' => Venue::SPORT_BALL,
                'image' => 'images/locations/alliston_arena.png',
            ],
            [
                'name' => 'Thornton Indoor Rink',
                'address' => '242 Barrie St, Thornton, ON',
                'directions' => null,
                'sport' => Venue::SPORT_ICE,
                'image' => 'images/locations/thornton_rink.gif',
            ],
            [
                'name' => 'Innisfil Recreation Centre (YMCA)',
                'address' => '7315 Yonge St, Innisfil, ON L9S 2M6',
                'directions' => 'Home of Summer Ice Hockey.',
                'sport' => Venue::SPORT_ICE,
                'image' => 'images/locations/summer_ice.png',
            ],
        ];

        foreach ($venues as $i => $venue) {
            Venue::create($venue + ['sort_order' => $i, 'date_created' => date('Y-m-d')]);
        }
        $messages[] = "seeded " . count($venues) . " venues.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
