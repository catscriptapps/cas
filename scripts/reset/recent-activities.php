<?php
// /scripts/reset/recent-activities.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\RecentActivity;

function resetRecentActivitiesTable(): array
{
    $messages = [];

    try {
        $tableName = (new RecentActivity())->getTable();

        // 1. Drop existing table
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        // 2. Recreate table structure
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            // Change this from increments to bigIncrements for the primary key
            $table->bigIncrements('id');

            // CHANGE THIS: Must be unsignedBigInteger to match the Users BigInt ID
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('action');
            $table->string('category')->nullable()->index();
            $table->string('entity_type')->nullable();

            // If your entities (Invoices/Customers) also use BigInt IDs, change this too
            $table->unsignedBigInteger('entity_id')->nullable()->index();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('archived')->default(false)->index();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}
