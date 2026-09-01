<?php
// /scripts/reset/registrant-accounts.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\RegistrantAccount;

/**
 * Login credentials for the self-service registrant dashboard -- see
 * server/models/RegistrantAccount.php for why this is a separate,
 * email-keyed table rather than a password column on `registrations`.
 * Empty on every reset; a registrant creates their own row either while
 * registering (an optional password field) or later via the existing
 * forgot-password flow, which doubles as first-time setup.
 */
function resetRegistrantAccountsTable(): array
{
    $messages = [];
    $tableName = (new RegistrantAccount())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->string('email', 300)->primary();
            $table->string('password', 255);
            $table->datetime('created_at')->nullable();
        });
        $messages[] = "created '{$tableName}' table structure.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
