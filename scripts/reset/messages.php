<?php
// /scripts/reset/messages.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Message;

function resetMessagesTable(): array
{
    $messages = [];

    try {
        $tableName = (new Message())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) use ($tableName) {
            $table->increments('id');

            // --- Threading Columns ---
            // conversation_id: Groups all messages in a back-and-forth
            $table->string('conversation_id')->nullable()->index();

            // parent_id: Points to the specific message being replied to
            $table->unsignedInteger('parent_id')->nullable()->index();

            // Contact info
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();

            $table->string('subject')->index();
            $table->text('message');

            $table->boolean('is_read')->default(false)->index();
            $table->boolean('is_sent')->default(false)->index();
            $table->boolean('is_draft')->default(false)->index();
            $table->boolean('is_archived')->default(false)->index();

            $table->timestamps();

            // Optional: Foreign key to self for data integrity
            $table->foreign('parent_id')
                ->references('id')
                ->on($tableName)
                ->onDelete('set null');
        });

        $messages[] = "{$tableName} table created successfully with threading support.";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}
