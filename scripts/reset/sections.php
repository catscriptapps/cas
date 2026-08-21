<?php
// /scripts/reset/sections.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Section;

/**
 * Resets the sections table -- the tabs a Company Admin organizes their
 * question bank into (Roofing, Electrical, Plumbing, etc). Seeded from
 * HomeWorks Advantages Inc.'s legacy CanNACHI-standard inspection report,
 * which becomes the default question-bank template every company starts
 * with (customizable afterward).
 */
function resetSectionsTable(): array
{
    $messages = [];
    try {
        $tableName = 'sections';

        Capsule::schema()->disableForeignKeyConstraints();
        Capsule::schema()->dropIfExists($tableName);

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->string('name', 300);
            $table->string('icon', 50)->nullable();
            $table->integer('pos_index')->default(0);
            $table->integer('status_id')->default(1);
            $table->datetime('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });

        $messages[] = "created 'sections' table structure.";

        // Order: name, icon -- seeded identically for every company as the
        // platform's default starter template.
        $sectionDefs = [
            ['Roofing', 'roof'],
            ['Exterior', 'exterior'],
            ['Structure', 'structure'],
            ['Insulation and Ventilation', 'documents'],
            ['Electrical', 'electrical'],
            ['Heating and Cooling', 'hvac'],
            ['Plumbing', 'plumbing'],
            ['Interior', 'general'],
        ];

        $companyIds = [1, 2];

        $count = 0;
        foreach ($companyIds as $companyId) {
            foreach ($sectionDefs as $posIndex => [$name, $icon]) {
                Section::create([
                    'company_id'   => $companyId,
                    'name'         => $name,
                    'icon'         => $icon,
                    'pos_index'    => $posIndex,
                    'status_id'    => 1,
                    'date_created' => date('Y-m-d'),
                ]);
                $count++;
            }
        }

        $messages[] = "Successfully imported $count sections.";
    } catch (\Throwable $e) {
        $messages[] = 'sections table error: ' . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}
