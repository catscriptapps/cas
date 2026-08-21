<?php
// /scripts/reset/password-resets.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PasswordReset;

/**
 * Resets the password_resets table for the Authentication module.
 */
function resetPasswordResetsTable(): array
{
    $messages = [];

    try {
        $tableName = (new PasswordReset())->getTable();

        // Drop existing table if it exists
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        // Create table
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            // We use email as the primary key since one user only needs one active reset token
            $table->string('email')->primary();
            $table->string('token');
            // We use a simple timestamp for 'created_at' to handle expiration logic
            $table->timestamp('created_at')->nullable();

            // Optimization index for quick lookups
            $table->index('email');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = 'Password Resets table error: ' . $e->getMessage();
    }

    return $messages;
}
