<?php
// /scripts/reset/slideshow.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Slide;

/**
 * Seeds the home hero's rotating background with the same curated set of
 * stock images that used to be hardcoded directly in layout-header.php --
 * this table (plus the new /slideshow admin page) is what makes them
 * add/reorder/delete-able without a code deploy.
 */
function resetSlideshowTable(): array
{
    $messages = [];
    $tableName = (new Slide())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('slide_id');
            $table->string('filename', 255);
            $table->integer('sort_order')->default(0);
            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "created '{$tableName}' table structure.";

        $defaults = ['hero-1.png', 'hero-2.png', 'hero-4.png', 'hero-7.png', 'hero-9.png', 'hero-11.png', 'hero-13.png', 'hero-17.png'];

        foreach ($defaults as $i => $filename) {
            Slide::create([
                'filename' => 'images/home/' . $filename,
                'sort_order' => $i,
                'date_created' => date('Y-m-d'),
            ]);
        }
        $messages[] = "seeded " . count($defaults) . " slideshow images.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
