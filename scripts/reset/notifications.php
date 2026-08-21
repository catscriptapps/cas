<?php
// /scripts/reset/notifications.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Notification;

/**
 * Resets the notifications table (in-app alerts for tenants/landlords/users,
 * e.g. a rental application being approved or declined).
 */
function resetNotificationsTable(): array
{
    $messages = [];

    try {
        $model = new Notification();
        $tableName = $model->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table.";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('recipient_type', 20)->index(); // 'tenant' | 'landlord' | 'user'
            $table->unsignedInteger('recipient_id')->index();
            $table->string('type', 50);
            $table->string('subject', 300);
            $table->text('message');
            $table->string('link', 300)->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrent()->useCurrentOnUpdate();

            $table->index(['recipient_type', 'recipient_id', 'is_read']);
        });

        $messages[] = "created {$tableName} table structure (no seeding).";
    } catch (\Throwable $e) {
        $messages[] = 'notifications table error: ' . $e->getMessage();
    }

    return $messages;
}
