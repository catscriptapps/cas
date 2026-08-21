<?php
// /scripts/reset/standards.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Company;
use Src\Controller\StandardsController;

/**
 * Resets the Standards module -- a per-company, ordered sequence of
 * rich-text "pages" (legacy's Standards of Practice boilerplate). Each row
 * is one page of free-form HTML; there's no title/category/section
 * grouping, matching legacy's hit_standards_txts table exactly in shape,
 * just with cleaner column names (content instead of page/page_id) and an
 * actually-enforced pos_index ordering, which legacy defined but never
 * applied in its queries. After recreating the table, seeds every company
 * with the default CanNACHI Standards of Practice pages (see
 * StandardsController::seedDefaults()) -- the same seeding a brand-new
 * company gets automatically via CompaniesController::save().
 */
function resetStandardsTable(): array
{
    $messages = [];
    try {
        Capsule::schema()->dropIfExists('standards');

        Capsule::schema()->create('standards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->longText('content');
            $table->integer('pos_index')->default(0);
            $table->timestamps();
        });

        $messages[] = "table 'standards' created.";

        $seededCompanies = 0;
        foreach (Company::all() as $company) {
            StandardsController::seedDefaults((int)$company->id);
            $seededCompanies++;
        }
        $messages[] = "seeded default Standards of Practice pages for {$seededCompanies} company(ies).";
    } catch (\Throwable $e) {
        $messages[] = "ERROR resetting standards: " . $e->getMessage();
    }
    return $messages;
}
