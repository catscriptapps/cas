<?php
// /scripts/reset/user-types.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\UserType;

/**
 * Resets the users_types table and seeds default professional roles.
 */
function resetUserTypesTable(): array
{
    $messages = [];

    try {
        $model = new UserType();
        $tableName = $model->getTable();

        // Drop existing table
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table.";

        // Create table matching legacy SQL structure
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            // This handles everything: Primary, Auto-increment, and Int type.
            $table->increments('user_type_id');
            $table->string('user_type', 300)->nullable();
        });

        $messages[] = "created {$tableName} table structure.";

        // Seed data with professional formatting (No underscores!)
        $defaultTypes = [
            ['user_type_id' => 1, 'user_type' => 'Admin'],
            ['user_type_id' => 2, 'user_type' => 'Company Admin'],
            ['user_type_id' => 3, 'user_type' => 'Inspector'],
        ];

        foreach ($defaultTypes as $type) {
            UserType::create($type);
        }

        $messages[] = "successfully seeded " . count($defaultTypes) . " user types.";
    } catch (\Throwable $e) {
        $messages[] = 'user Types table error: ' . $e->getMessage();
    }

    return $messages;
}
