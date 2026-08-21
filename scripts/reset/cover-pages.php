<?php
// /scripts/reset/cover-pages.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Resets the Cover Pages module -- a per-company gallery of images that get
 * stitched onto the front and back of every finished PDF report for that
 * company (see PDF generation, not yet built). Mirrors legacy's
 * hit_cover_pages table exactly: an image filename, a shared pos_index
 * ordering across both groups, and a page_location flag (1 = Front,
 * 2 = Back) rather than one row per company or per inspection.
 */
function resetCoverPagesTable(): array
{
    $messages = [];
    try {
        Capsule::schema()->dropIfExists('cover_pages');

        Capsule::schema()->create('cover_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->string('image_name', 300);
            $table->unsignedTinyInteger('page_location')->default(1); // 1 = Front, 2 = Back
            $table->integer('pos_index')->default(0);
            $table->timestamps();
        });

        $messages[] = "table 'cover_pages' created.";
    } catch (\Throwable $e) {
        $messages[] = "ERROR resetting cover_pages: " . $e->getMessage();
    }
    return $messages;
}
