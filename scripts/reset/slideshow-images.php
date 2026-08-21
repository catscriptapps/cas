<?php
// /scripts/reset/slideshow-images.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Slideshow;

/**
 * Resets the slideshow_images table structure and seeds the default
 * homepage/global header slideshow pictures.
 */
function resetSlideshowImagesTable(): array
{
    $messages = [];

    try {
        $tableName = (new Slideshow())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "Dropped existing {$tableName} table.";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('image_name', 300);
            $table->integer('pos_index')->default(0);
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->timestamps();
        });

        $messages[] = "Created {$tableName} table.";

        // ---------------------------------------------------------
        // Default slideshow images (moved out of layout-header.php)
        // ---------------------------------------------------------
        $defaultImages = [
            '1.jpg',
            '2.jpg',
            '3.jpg',
            '4.jpg',
            '5.jpg',
            '6.jpg',
            '7.jpg',
            '8.jpg',
            '9.jpg',
            '10.jpg',
            '11.jpg',
            '12.jpg',
            '13.jpg',
            '14.jpg',
        ];

        foreach ($defaultImages as $index => $imageName) {
            Slideshow::create([
                'image_name' => $imageName,
                'pos_index'  => $index,
                'status_id'  => 1,
            ]);
        }

        $messages[] = "Seeded " . count($defaultImages) . " default slideshow images.";
    } catch (\Throwable $e) {
        $messages[] = "Error resetting slideshow_images table: " . $e->getMessage();
    }

    return $messages;
}
