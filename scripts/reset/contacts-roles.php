<?php
// /scripts/reset/contacts-roles.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ContactRole;

function resetContactRolesTable(): array
{
    $messages = [];
    $tableName = (new ContactRole())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('role_name', 100);
            $table->string('role_slug', 100)->nullable();
        });
        $messages[] = "fresh {$tableName} table created.";

        $data = [
            ['id' => 1, 'role_name' => 'Timekeeper', 'role_slug' => 'timekeeper'],
            ['id' => 2, 'role_name' => 'Referee - Ball Hockey', 'role_slug' => 'ref-ball-hockey'],
            ['id' => 3, 'role_name' => 'Referee - Ice Hockey', 'role_slug' => 'ref-ice-hockey'],
            ['id' => 4, 'role_name' => 'Township / City', 'role_slug' => 'township-city'],
        ];

        foreach ($data as $item) {
            ContactRole::create($item);
        }

        $messages[] = "seeded " . count($data) . " contact roles.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
