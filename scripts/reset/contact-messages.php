<?php
// /scripts/reset/contact-messages.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ContactMessage;

/**
 * Inbox for the public "Contact Us" form -- a standalone admin record with
 * no FKs to any other table (same shape as IncidentReport). Seeds two
 * sample messages so the admin Messages page/unread badge have something to
 * show right after a fresh reset; real submissions come in via
 * server/api/contact.php from then on.
 */
function resetContactMessagesTable(): array
{
    $messages = [];
    $tableName = (new ContactMessage())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->string('full_name', 200);
            $table->string('email', 200);
            $table->string('subject', 300);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->integer('status_id')->default(1);
            $table->timestamps();
        });
        $messages[] = "fresh {$tableName} table created.";

        Capsule::table($tableName)->insert([
            [
                'full_name' => 'Jamie Reyes',
                'email' => 'jamie.reyes@example.com',
                'subject' => 'Question about Mens Ice registration',
                'message' => "Hi there,\n\nI'm trying to register for the Mens Ice league but the 2025 season isn't showing up as an option for me. Is registration still open?\n\nThanks,\nJamie",
                'is_read' => 0,
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'full_name' => 'Pat Chen',
                'email' => 'pat.chen@example.com',
                'subject' => 'Sponsorship inquiry',
                'message' => "Hello,\n\nOur company is interested in sponsoring a youth team next season. Could someone send over pricing and options?\n\nBest,\nPat",
                'is_read' => 1,
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
        ]);
        $messages[] = 'seeded 2 sample contact messages.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
