<?php
// /scripts/migrate-photo-video-library.php
//
// One-off, non-destructive migration: splits the old direct-to-section
// inspection_pictures/inspection_videos tables into an inspection-scoped
// library (no section_id) plus junction tables for section (and, for
// photos, contract) assignment. Preserves every existing row's current
// section, caption, and order by copying it into the new junction table
// before dropping the old columns. Safe to run more than once -- it skips
// the column drop if the columns are already gone.
//
// Run once from the project root: php scripts/migrate-photo-video-library.php

declare(strict_types=1);

require __DIR__ . '/../server/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

function columnExists(string $table, string $column): bool
{
    return Capsule::schema()->hasColumn($table, $column);
}

echo "Migrating photo library...\n";

if (!Capsule::schema()->hasTable('inspection_picture_sections')) {
    Capsule::schema()->create('inspection_picture_sections', function ($table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('inspection_id');
        $table->unsignedBigInteger('picture_id');
        $table->unsignedBigInteger('section_id');
        $table->string('description', 500)->nullable();
        $table->integer('pos_index')->default(0);
        $table->datetime('date_created')->nullable();
        $table->unique(['picture_id', 'section_id']);
    });
    echo "  created inspection_picture_sections\n";
}

if (!Capsule::schema()->hasTable('inspection_picture_contracts')) {
    Capsule::schema()->create('inspection_picture_contracts', function ($table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('inspection_id');
        $table->unsignedBigInteger('picture_id');
        $table->string('description', 500)->nullable();
        $table->integer('pos_index')->default(0);
        $table->datetime('date_created')->nullable();
        $table->unique('picture_id');
    });
    echo "  created inspection_picture_contracts\n";
}

if (columnExists('inspection_pictures', 'section_id')) {
    $rows = Capsule::table('inspection_pictures')->get();
    foreach ($rows as $row) {
        Capsule::table('inspection_picture_sections')->insertOrIgnore([
            'inspection_id' => $row->inspection_id,
            'picture_id' => $row->id,
            'section_id' => $row->section_id,
            'description' => $row->description,
            'pos_index' => $row->pos_index,
            'date_created' => $row->date_created,
        ]);
    }
    echo "  migrated {$rows->count()} picture(s) into inspection_picture_sections\n";

    Capsule::schema()->table('inspection_pictures', function ($table) {
        $table->dropColumn(['section_id', 'description', 'pos_index']);
    });
    echo "  dropped section_id/description/pos_index from inspection_pictures\n";
} else {
    echo "  inspection_pictures already migrated, skipping\n";
}

echo "Migrating video library...\n";

if (!Capsule::schema()->hasTable('inspection_video_sections')) {
    Capsule::schema()->create('inspection_video_sections', function ($table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('inspection_id');
        $table->unsignedBigInteger('video_id');
        $table->unsignedBigInteger('section_id');
        $table->string('description', 500)->nullable();
        $table->integer('pos_index')->default(0);
        $table->datetime('date_created')->nullable();
        $table->unique(['video_id', 'section_id']);
    });
    echo "  created inspection_video_sections\n";
}

if (columnExists('inspection_videos', 'section_id')) {
    $rows = Capsule::table('inspection_videos')->get();
    foreach ($rows as $row) {
        Capsule::table('inspection_video_sections')->insertOrIgnore([
            'inspection_id' => $row->inspection_id,
            'video_id' => $row->id,
            'section_id' => $row->section_id,
            'description' => $row->description,
            'pos_index' => $row->pos_index,
            'date_created' => $row->date_created,
        ]);
    }
    echo "  migrated {$rows->count()} video(s) into inspection_video_sections\n";

    Capsule::schema()->table('inspection_videos', function ($table) {
        $table->dropColumn(['section_id', 'description', 'pos_index']);
    });
    echo "  dropped section_id/description/pos_index from inspection_videos\n";
} else {
    echo "  inspection_videos already migrated, skipping\n";
}

echo "Done.\n";
