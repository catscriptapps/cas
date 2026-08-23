<?php
// /scripts/reset/sources.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Source;

/**
 * "How did you hear about us?" lookup shown on the registration form.
 */
function resetSourcesTable(): array
{
    $messages = [];
    $tableName = (new Source())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->string('hear_about_us', 300);
            $table->integer('status_id')->default(1);
            $table->integer('display_order')->default(0);
            $table->date('date_created')->nullable();
        });
        $messages[] = "created '{$tableName}' table structure.";

        $data = [
            'Social Media',
            'Website',
            'Word of Mouth / Friend',
            'Returning Player',
            'Newsletter',
            'Other',
        ];

        foreach ($data as $i => $label) {
            Source::create([
                'hear_about_us' => $label,
                'status_id' => 1,
                'display_order' => $i,
                'date_created' => date('Y-m-d'),
            ]);
        }

        $messages[] = "seeded " . count($data) . " sources.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
