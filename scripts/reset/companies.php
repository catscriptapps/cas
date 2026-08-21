<?php
// /scripts/reset/companies.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Company;

/**
 * Resets the companies table and seeds the two founding companies.
 */
function resetCompaniesTable(): array
{
    $messages = [];
    try {
        $tableName = 'companies';

        Capsule::schema()->disableForeignKeyConstraints();
        Capsule::schema()->dropIfExists($tableName);

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_name', 300);
            $table->string('email', 300)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('toll_free', 40)->nullable();
            $table->string('website', 300)->nullable();
            $table->string('slogan', 300)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('city', 300)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('logo_url')->nullable();
            $table->text('general_summary')->nullable();
            $table->integer('status_id')->default(1);
            $table->datetime('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });

        $messages[] = "created 'companies' table structure.";

        // Order: id, company_name, email, phone, toll_free, website, slogan,
        // address, city, country_id, region_id, postal_code
        $companiesData = [
            [1, 'CatScript Apps', 'catscriptapps@gmail.com', '(705) 333-1650', null, 'https://catscriptapps.com', 'Making Your Apps Come Alive', '11 Girdwood Drive', 'Barrie', 39, 866, 'L4N 8P5'],
            [2, 'HomeWorks Advantages Inc.', 'homeworks.inspections.canada@gmail.com', '(705) 424-2800', '1 800 249 6757', 'https://homeworksinspections.info', 'Helping Make A House Your Home', '5 Centre Street', 'Angus', 39, 866, 'L0M 1B0'],
        ];

        $count = 0;
        foreach ($companiesData as $row) {
            Company::create([
                'id'           => $row[0],
                'company_name' => $row[1],
                'email'        => $row[2],
                'phone'        => $row[3],
                'toll_free'    => $row[4],
                'website'      => $row[5],
                'slogan'       => $row[6],
                'address'      => $row[7],
                'city'         => $row[8],
                'country_id'   => $row[9],
                'region_id'    => $row[10],
                'postal_code'  => $row[11],
                'status_id'    => 1,
                'date_created' => date('Y-m-d'),
            ]);
            $count++;
        }

        $messages[] = "Successfully imported $count companies.";
    } catch (\Throwable $e) {
        $messages[] = 'companies table error: ' . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}
