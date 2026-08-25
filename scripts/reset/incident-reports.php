<?php
// /scripts/reset/incident-reports.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\IncidentReport;

/**
 * On-floor incident log -- a standalone admin record with no FKs to any
 * other table (see IncidentReport model docblock). Seeds the real 45 rows
 * from legacy cas-sports (2018-2023), see scripts/reset/data/
 * incident-reports.php, a generated fixture. entry_id preserved exactly.
 */
function resetIncidentReportsTable(): array
{
    $messages = [];
    $tableName = (new IncidentReport())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->date('incident_date')->nullable();
            $table->string('incident_time', 50)->nullable();
            $table->text('teams_involved')->nullable();
            $table->text('persons_involved')->nullable();
            $table->string('location', 200)->nullable();
            $table->string('ref_involved', 300)->nullable();
            $table->string('timekeeper', 300)->nullable();
            $table->text('incident')->nullable();
            $table->text('equipment_worn')->nullable();
            $table->text('medical_assistance')->nullable();
            $table->string('manager_name', 300)->nullable();
            $table->string('manager_time', 50)->nullable();
            $table->text('referee_outcome')->nullable();
            $table->text('name_e_signature')->nullable();
            $table->integer('status_id')->default(1);
        });
        $messages[] = "fresh {$tableName} table created.";

        $reports = require __DIR__ . '/data/incident-reports.php';
        Capsule::table($tableName)->insert($reports);
        $messages[] = 'seeded ' . count($reports) . ' incident reports from legacy cas-sports.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
