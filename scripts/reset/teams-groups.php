<?php
// /scripts/reset/teams-groups.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\TeamGroup;

/**
 * The "Group" dropdown on the Team form (A/B/C1/D2/etc) -- seeded with the
 * exact same 20 legacy cas-sports labels, same sort order.
 */
function resetTeamsGroupsTable(): array
{
    $messages = [];
    $tableName = (new TeamGroup())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('group_name', 50)->unique();
            $table->integer('sort_order')->default(0);
        });
        $messages[] = "fresh {$tableName} table created.";

        $groupNames = [
            'A', 'B', 'B1', 'B2', 'C', 'C1', 'C2', 'C3', 'D', 'D1', 'D2',
            'Competitive', 'Competitive 1', 'Competitive 2',
            'Intermediate', 'Intermediate1', 'Intermediate2',
            'Open', 'Open1', 'Open2',
        ];

        foreach ($groupNames as $i => $name) {
            TeamGroup::create(['group_name' => $name, 'sort_order' => ($i + 1) * 10]);
        }

        $messages[] = 'seeded ' . count($groupNames) . ' team groups.';
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
