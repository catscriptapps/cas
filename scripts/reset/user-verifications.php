<?php
// /scripts/reset/user-verifications.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\UserVerification;

/**
 * Resets the user_verifications table for the Guest Registration module.
 */
function resetUserVerificationsTable(): array
{
    $messages = [];

    try {
        $model = new UserVerification();
        $tableName = $model->getTable();

        // Drop existing table if it exists
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        // Create table
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            // We use email as the primary key for identity mapping
            $table->string('email')->primary();
            $table->string('token');

            // Standard timestamp for expiration logic (e.g., 60-minute window)
            $table->timestamp('created_at')->nullable();

            // Indexing for faster lookups during the verification click
            $table->index('email');
            $table->index('token');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = 'User Verifications table error: ' . $e->getMessage();
    }

    return $messages;
}
