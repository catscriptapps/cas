<?php
// /scripts/reset/sponsors.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Sponsor;

/**
 * Seeds the 7 real sponsor logos already sitting in public/images/sponsors/
 * (copied from legacy essahockey_live/images/sponsors/ in an earlier pass).
 * Legacy had these hardcoded in PHP with zero admin CRUD -- this table is
 * what makes the Sponsorship page's admin add/preview/delete controls
 * possible without a code deploy for every roster change.
 */
function resetSponsorsTable(): array
{
    $messages = [];
    $tableName = (new Sponsor())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('sponsor_id');
            $table->string('name', 255);
            $table->string('filename', 255);
            $table->integer('sort_order')->default(0);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "created '{$tableName}' table structure.";

        $partners = [
            ['name' => 'KM Repairs (Thornton)', 'filename' => 'km_repairs_thornton.png'],
            ['name' => 'Purely Canadian H2O Inc.', 'filename' => 'purely_canadian_h2o_inc.png'],
            ['name' => 'Thornton Pharmacy', 'filename' => 'thornton_pharmacy.png'],
            ['name' => 'Thornton Animal Clinic', 'filename' => 'thornton_animal_clinic.png'],
            ['name' => 'Tim Gilman', 'filename' => 'tim_gilman.png'],
            ['name' => 'The Last Shot Bar & Grill', 'filename' => 'last_shot_bar_and_grill.png'],
            ['name' => "Tony's Barber Zone", 'filename' => 'tonys_barber_zone.avif'],
        ];

        foreach ($partners as $i => $partner) {
            Sponsor::create($partner + ['sort_order' => $i, 'date_created' => date('Y-m-d')]);
        }
        $messages[] = "seeded " . count($partners) . " sponsors.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
