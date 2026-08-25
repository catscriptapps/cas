<?php
// /server/api/reset.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

header('Content-Type: application/json');

$messages = [];

/**
 * 1. PRE-FLIGHT CHECKS & DISABLE CONSTRAINTS
 */
Capsule::schema()->disableForeignKeyConstraints();

/**
 * 2. AGGRESSIVE DROP PHASE (Children first, then Parents)
 * This ensures we don't have "ghost" tables blocking the reset scripts.
 */
$tablesToDrop = [
    'password_resets',
    'recent_activities',
    'contacts',
    'contacts_roles',
    'stats',
    'player_stats',
    'schedules',
    'players',
    'teams',
    'seasons',
    'teams_groups',
    'locations',
    'registrations',
    'divisions',
    'leagues',
    'sports',
    'sources',
    'users',
    'users_types',
    'regions',
    'countries',
];

foreach ($tablesToDrop as $table) {
    Capsule::schema()->dropIfExists($table);
}

$messages[] = "database cleared: All dependent and parent tables dropped.";

/**
 * 3. CREATION PHASE - LEVEL 1: LOOKUPS & INDEPENDENT PARENTS
 * These must exist first because other tables reference them.
 */

// Core Users & Types
require_once __DIR__ . '/../../scripts/reset/user-types.php';
$messages = array_merge($messages, resetUserTypesTable());

require_once __DIR__ . '/../../scripts/reset/countries.php';
$messages = array_merge($messages, resetCountriesTable());

require_once __DIR__ . '/../../scripts/reset/regions.php';
$messages = array_merge($messages, resetRegionsTable());

require_once __DIR__ . '/../../scripts/reset/users.php';
$messages = array_merge($messages, resetUsersTable());

// League Management (minimal -- enough for the registration form to work;
// full admin CRUD for these is a future task)
require_once __DIR__ . '/../../scripts/reset/sports.php';
$messages = array_merge($messages, resetSportsTable());

require_once __DIR__ . '/../../scripts/reset/leagues.php';
$messages = array_merge($messages, resetLeaguesTable());

require_once __DIR__ . '/../../scripts/reset/divisions.php';
$messages = array_merge($messages, resetDivisionsTable());

require_once __DIR__ . '/../../scripts/reset/sources.php';
$messages = array_merge($messages, resetSourcesTable());

require_once __DIR__ . '/../../scripts/reset/registrations.php';
$messages = array_merge($messages, resetRegistrationsTable());

// Schedules / Teams / Players -- rebuilt from legacy cas-sports' 3 active
// seasons (596 real games, 65 real teams, 960 real roster slots across
// legacy's whole history; this imports the 3 currently-active seasons'
// worth exactly). teams_groups/locations/seasons are independent lookups;
// teams depends on seasons + the real registrants importRegistrationsTable()
// just seeded; players depends on teams; schedules depends on seasons,
// teams, and locations.
require_once __DIR__ . '/../../scripts/reset/teams-groups.php';
$messages = array_merge($messages, resetTeamsGroupsTable());

require_once __DIR__ . '/../../scripts/reset/locations.php';
$messages = array_merge($messages, resetLocationsTable());

require_once __DIR__ . '/../../scripts/reset/seasons.php';
$messages = array_merge($messages, resetSeasonsTable());

require_once __DIR__ . '/../../scripts/reset/teams.php';
$messages = array_merge($messages, resetTeamsTable());

require_once __DIR__ . '/../../scripts/reset/players.php';
$messages = array_merge($messages, resetPlayersTable());

require_once __DIR__ . '/../../scripts/reset/schedules.php';
$messages = array_merge($messages, resetSchedulesTable());

// Stats / Standings -- independent of Schedules/Gamesheets (there's no
// score data anywhere in this app; win/loss/tie and goal totals are
// maintained entirely by hand). Both just need teams/players to already
// exist for their team_id/player_id references.
require_once __DIR__ . '/../../scripts/reset/stats.php';
$messages = array_merge($messages, resetStatsTable());

require_once __DIR__ . '/../../scripts/reset/player-stats.php';
$messages = array_merge($messages, resetPlayerStatsTable());

// Contact Directory (league officials, timekeepers, emergency/township
// contacts) -- contacts_roles must exist before contacts since contacts.
// role_id references it.
require_once __DIR__ . '/../../scripts/reset/contacts-roles.php';
$messages = array_merge($messages, resetContactRolesTable());

require_once __DIR__ . '/../../scripts/reset/contacts.php';
$messages = array_merge($messages, resetContactsTable());

// Support & Transient Auth Tables
require_once __DIR__ . '/../../scripts/reset/recent-activities.php';
$messages = array_merge($messages, resetRecentActivitiesTable());

require_once __DIR__ . '/../../scripts/reset/password-resets.php';
$messages = array_merge($messages, resetPasswordResetsTable());

/**
 * 4. Everything past Authentication & Core / Static Lookups -- the
 * Home Comfort Reports inspection modules (companies, questions, sections,
 * inspections, cover pages, standards, section diagrams, note hints,
 * notifications, faqs, slideshow, messages, user verifications) are
 * intentionally not (re)created; their reset scripts were removed along
 * with the tables themselves.
 */

/**
 * 5. FINALIZE
 */
Capsule::schema()->enableForeignKeyConstraints();

$deleteAllPicturesAndPDFs = true;

// --- DELETE specific transient application upload content only ---
if ($deleteAllPicturesAndPDFs) {
    $targetFolders = [
        __DIR__ . '/../../public/images/uploads',
        __DIR__ . '/../../public/videos',
        __DIR__ . '/../../public/pdfs',
    ];

    foreach ($targetFolders as $folder) {
        $resolved = realpath($folder);

        // Skip if the folder doesn't exist to avoid errors
        if ($resolved === false || !is_dir($resolved)) {
            $messages[] = "Skipping: folder not found: $folder";
            continue;
        }

        $messages[] = "cleaning contents of: $resolved";

        $entries = scandir($resolved);
        if ($entries === false) continue;

        $deletedCount = 0;
        foreach ($entries as $entry) {
            // NEVER delete current, parent, or .gitkeep (keeps the folder structure in Git)
            if (in_array($entry, ['.', '..', '.gitkeep'])) continue;

            $path = $resolved . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                // If it's a subfolder, delete it and its contents
                if (rrmdir($path)) $deletedCount++;
            } else {
                // If it's a file, delete it
                if (unlink($path)) $deletedCount++;
            }
        }

        $messages[] = "purged $deletedCount item(s) from $folder. (Avatars preserved)";
    }
}

json_response(['success' => true, 'messages' => $messages]);
