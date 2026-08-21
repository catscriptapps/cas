<?php
// /scripts/reset/inspections.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Inspection;
use App\Models\Section;

/**
 * Resets the Inspections module -- the inspection header table plus its
 * child answer tables (per-question tasks/notes, selected checkbox options,
 * filled-in-blank field responses, per-section comments, and per-section
 * photos). Seeds a handful of demo inspections for both companies spanning
 * every row-color state (no report yet, expired, expiring imminently,
 * expiring soon, healthy) so the list page's status coloring is visible
 * immediately without needing to manually finalize several inspections
 * first.
 */
function resetInspectionsTable(): array
{
    $messages = [];
    try {
        Capsule::schema()->disableForeignKeyConstraints();

        Capsule::schema()->dropIfExists('inspection_picture_contracts');
        Capsule::schema()->dropIfExists('inspection_picture_sections');
        Capsule::schema()->dropIfExists('inspection_video_sections');
        Capsule::schema()->dropIfExists('inspection_pictures');
        Capsule::schema()->dropIfExists('inspection_videos');
        Capsule::schema()->dropIfExists('inspection_section_comments');
        Capsule::schema()->dropIfExists('inspection_question_fields');
        Capsule::schema()->dropIfExists('inspection_question_options');
        Capsule::schema()->dropIfExists('inspection_questions');
        Capsule::schema()->dropIfExists('inspections');

        Capsule::schema()->create('inspections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('orig_user_id'); // creator / assigned inspector
            $table->string('property_address', 500);
            $table->string('city', 300)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->string('postal_code', 50)->nullable();
            $table->string('access_code', 20)->nullable();
            // Inspector's initials, printed in every section's footer legend
            // on the PDF and editable from any section's footer on the
            // fill-in screen -- mirrors legacy's hit_inspections.initials.
            $table->string('initials', 10)->nullable();
            $table->text('inspection_summary')->nullable();
            $table->integer('status_id')->default(1);
            // Reserved for the future PDF-generation task -- always null for now.
            $table->string('file_name', 300)->nullable();
            // Set together when a report is "finalized" (see InspectionsController::finalize()).
            // date_expires is what the list page's row-color logic keys off of.
            $table->datetime('date_posted')->nullable();
            $table->datetime('date_expires')->nullable();
            $table->datetime('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });

        Capsule::schema()->create('inspection_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('question_id');
            // 0=blank, 1=n/a, 2=Inspected, 3=Not Inspected -- mirrors legacy's Tasks select.
            $table->unsignedTinyInteger('tasks')->default(0);
            $table->text('notes')->nullable();
        });

        Capsule::schema()->create('inspection_question_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('question_option_id');
        });

        Capsule::schema()->create('inspection_question_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('question_field_id');
            $table->text('response_text')->nullable();
        });

        Capsule::schema()->create('inspection_section_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('section_id');
            $table->text('comments')->nullable();
        });

        // Photos live in one inspection-scoped library (upload once, no
        // section required) and get assigned to zero or more sections and/or
        // the Contracts page via the junction tables below -- mirrors
        // legacy's hit_inspections_pics / hit_inspections_pics_sections
        // split rather than the old one-photo-one-section model.
        Capsule::schema()->create('inspection_pictures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->string('filename', 300);
            $table->datetime('date_created')->nullable();
        });

        Capsule::schema()->create('inspection_picture_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('picture_id');
            $table->unsignedBigInteger('section_id');
            $table->string('description', 500)->nullable();
            $table->integer('pos_index')->default(0);
            $table->datetime('date_created')->nullable();
            $table->unique(['picture_id', 'section_id']);
        });

        // A photo assigned as a contract document -- same shape as the
        // section junction but with only one possible "target", so at most
        // one row per picture. Prints right before Section 1 in the PDF.
        Capsule::schema()->create('inspection_picture_contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('picture_id');
            $table->string('description', 500)->nullable();
            $table->integer('pos_index')->default(0);
            $table->datetime('date_created')->nullable();
            $table->unique('picture_id');
        });

        // Videos -- same library + section-assignment split as photos
        // (no Contracts equivalent; legacy's contract slot is photos-only).
        Capsule::schema()->create('inspection_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->string('filename', 300);
            $table->datetime('date_created')->nullable();
        });

        Capsule::schema()->create('inspection_video_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('video_id');
            $table->unsignedBigInteger('section_id');
            $table->string('description', 500)->nullable();
            $table->integer('pos_index')->default(0);
            $table->datetime('date_created')->nullable();
            $table->unique(['video_id', 'section_id']);
        });

        $messages[] = "created 'inspections' table and its child answer/photo/video library tables.";

        // Order: company_id, orig_user_id, property_address, city, postal_code,
        // days_until_expiry (null = no report finalized yet -> white row)
        // Demo inspections only -- company #1 only, deliberately. Company #2
        // (HomeWorks Advantages Inc.) starts with an empty inspections list.
        $inspectionsData = [
            [1, 5, '36 Elm Street', 'Barrie', 'L4N 1A1', null],
            [1, 3, '142 Birch Avenue', 'Barrie', 'L4N 2B2', -6],   // expired -> red
            [1, 5, '58 Maple Court', 'Orillia', 'L3V 3C3', 1],     // expiring imminently -> strong red
            [1, 3, '9 Pine Ridge Road', 'Barrie', 'L4N 4D4', 5],   // expiring soon -> yellow
            [1, 5, '271 Cedar Lane', 'Orillia', 'L3V 5E5', 12],    // healthy -> green
        ];

        $count = 0;
        foreach ($inspectionsData as [$companyId, $origUserId, $address, $city, $postal, $daysUntilExpiry]) {
            $hasReport = $daysUntilExpiry !== null;
            $dateExpires = $hasReport ? date('Y-m-d H:i:s', strtotime("{$daysUntilExpiry} days")) : null;
            $datePosted = $hasReport ? date('Y-m-d H:i:s', strtotime("{$daysUntilExpiry} days -14 days")) : null;

            Inspection::create([
                'company_id'         => $companyId,
                'orig_user_id'       => $origUserId,
                'property_address'   => $address,
                'city'               => $city,
                'country_id'         => 39,  // Canada
                'region_id'          => 866, // Ontario
                'postal_code'        => $postal,
                'access_code'        => strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)),
                'status_id'          => 1,
                'date_posted'        => $datePosted,
                'date_expires'       => $dateExpires,
                'date_created'       => date('Y-m-d'),
            ]);
            $count++;
        }

        $messages[] = "Successfully imported $count demo inspections spanning every row-color state.";
    } catch (\Throwable $e) {
        $messages[] = 'inspections tables error: ' . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}
