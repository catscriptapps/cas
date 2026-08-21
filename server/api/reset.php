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
    'landlord_services',

    // Inspections (children first, then parents)
    'inspection_pictures',
    'inspection_videos',
    'inspection_section_comments',
    'inspection_question_fields',
    'inspection_question_options',
    'inspection_questions',
    'inspections',

    // Properties Maintenance (tenant<->landlord ticket conversations) -- children first
    'pmaint_ticket_media',
    'pmaint_ticket_messages',
    'pmaint_tickets',
    'property_tenants',

    // Maintenance (children first, then parents/lookups) -- mirrors Inspections
    'maint_report_tenants',
    'maint_category_videos',
    'maint_report_category_pics',
    'maint_report_pics',
    'maint_report_category_details',
    'maint_report_categories',
    'maint_reports',
    'maint_category_components',
    'maint_categories',
    'maint_types',

    // Dynamic Transactional Pipelines
    'ren_automobiles',
    'ren_employments',
    'ren_finances_obligations',
    'ren_finances',
    'ren_main_applicants',
    'ren_last_residences',
    'ren_other_occupants',
    'ren_references',
    'ren_required_docs',
    'ren_rental_applications',
    'access_tokens',
    'tenants',
    'subscriptions',
    'properties_pics',
    'properties',
    'inspectors',
    'landlords',

    // Authentication & Core
    'password_resets',
    'user_verifications', // Added here to ensure it gets cleared out aggressively
    'messages',
    'recent_activities',
    'notifications',
    'question_fields',
    'question_options',
    'questions',
    'sections',
    'users',
    'user_types',
    'companies',

    // Static/Lookup Tables
    'cities',
    'regions',
    'countries',
    'faqs',
    'services',
    'slideshow_images',
    'cover_pages',
    'standards',
    'section_diagrams',
    'note_hints',
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

require_once __DIR__ . '/../../scripts/reset/cities.php';
$messages = array_merge($messages, resetCitiesTable());

require_once __DIR__ . '/../../scripts/reset/companies.php';
$messages = array_merge($messages, resetCompaniesTable());

require_once __DIR__ . '/../../scripts/reset/users.php';
$messages = array_merge($messages, resetUsersTable());

require_once __DIR__ . '/../../scripts/reset/sections.php';
$messages = array_merge($messages, resetSectionsTable());

require_once __DIR__ . '/../../scripts/reset/questions.php';
$messages = array_merge($messages, resetQuestionsTable());

require_once __DIR__ . '/../../scripts/reset/inspections.php';
$messages = array_merge($messages, resetInspectionsTable());

require_once __DIR__ . '/../../scripts/reset/cover-pages.php';
$messages = array_merge($messages, resetCoverPagesTable());

require_once __DIR__ . '/../../scripts/reset/standards.php';
$messages = array_merge($messages, resetStandardsTable());

require_once __DIR__ . '/../../scripts/reset/section-diagrams.php';
$messages = array_merge($messages, resetSectionDiagramsTable());

require_once __DIR__ . '/../../scripts/reset/note-hints.php';
$messages = array_merge($messages, resetNoteHintsTable());

// Support & Transient Auth Tables
require_once __DIR__ . '/../../scripts/reset/recent-activities.php';
$messages = array_merge($messages, resetRecentActivitiesTable());

require_once __DIR__ . '/../../scripts/reset/notifications.php';
$messages = array_merge($messages, resetNotificationsTable());

require_once __DIR__ . '/../../scripts/reset/faqs.php';
$messages = array_merge($messages, resetFaqsTable());

require_once __DIR__ . '/../../scripts/reset/slideshow-images.php';
$messages = array_merge($messages, resetSlideshowImagesTable());

require_once __DIR__ . '/../../scripts/reset/password-resets.php';
$messages = array_merge($messages, resetPasswordResetsTable());

// Placed directly next to password resets following your dash naming standards
require_once __DIR__ . '/../../scripts/reset/user-verifications.php';
$messages = array_merge($messages, resetUserVerificationsTable());

require_once __DIR__ . '/../../scripts/reset/messages.php';
$messages = array_merge($messages, resetMessagesTable());


/**
 * 4. Everything past Authentication & Core / Static Lookups -- legacy
 * Subscriptions/Services/Properties/Maintenance modules -- is intentionally
 * not (re)created; their reset scripts were removed along with the tables
 * themselves and are on hold pending a future rebuild.
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
