<?php
// /scripts/reset/section-diagrams.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Resets the Section Diagrams module -- a per-company, per-section gallery
 * of images (e.g. a furnace wiring schematic, a septic layout) that get
 * stitched into every finished PDF report for that company, immediately
 * after that section's photos, full-bleed like Cover Pages. Mirrors
 * legacy's hit_sections_diagrams table: an image filename, a per-section
 * pos_index ordering, and an admin-facing caption (organizational only --
 * not printed on the full-bleed PDF page, same as Cover Pages has no
 * caption either).
 */
function resetSectionDiagramsTable(): array
{
    $messages = [];
    try {
        Capsule::schema()->dropIfExists('section_diagrams');

        Capsule::schema()->create('section_diagrams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('section_id');
            $table->string('image_name', 300);
            $table->string('caption', 500)->nullable();
            $table->integer('pos_index')->default(0);
            $table->timestamps();
        });

        $messages[] = "table 'section_diagrams' created.";
    } catch (\Throwable $e) {
        $messages[] = "ERROR resetting section_diagrams: " . $e->getMessage();
    }
    return $messages;
}
