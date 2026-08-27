<?php
// /scripts/reset/home-page-text.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\HomePageText;

/**
 * Seeds the single admin-editable "Our Mission" block shown on the home
 * page between the intro paragraph and the register buttons. Text matches
 * legacy essahockey_live's own seed copy (scripts/db_reset.php's
 * db_create_home_page_text()) -- already Canadian All Star Sports branded,
 * unlike the stale "Essa Hockey" wording found in the live-edited DB row.
 */
function resetHomePageTextTable(): array
{
    $messages = [];
    $tableName = (new HomePageText())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->text('text_content')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "fresh {$tableName} table created.";

        $ourMission = "<p>Canadian All Star Sports is dedicated to providing a safe and enjoyable environment "
            . "for kids and adults of all ages and skill levels to participate in, and enjoy the game of hockey.</p>"
            . "<p>We believe hockey should be affordable and accessible to everyone. We strive to contribute to "
            . "each individual's personal growth and skill development by promoting self-confidence, team work, "
            . "fair play, and sportsmanship. We do it for the love of the sport!</p>";

        HomePageText::create([
            'entry_id' => HomePageText::OUR_MISSION,
            'text_content' => $ourMission,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
        $messages[] = "seeded {$tableName} 'Our Mission' block.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
