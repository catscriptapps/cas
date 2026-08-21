<?php
// /scripts/reset/users.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\User;

function resetUsersTable(): array
{
    $messages = [];
    try {
        // 1. Force safety inside the script
        Capsule::schema()->disableForeignKeyConstraints();

        $tableName = 'users';
        Capsule::schema()->dropIfExists($tableName);

        // 2. Create structure -- a user is exactly one type (Admin, Company
        // Admin, or Inspector), so user_type_id is a plain scalar column,
        // not a collection.
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id'); // This is our BIGINT to match other tables
            $table->string('first_name', 300)->nullable();
            $table->string('last_name', 300)->nullable();
            $table->string('email', 300)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->string('city', 300)->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('password', 300)->nullable();
            $table->string('api_token', 300)->nullable();
            $table->integer('status_id')->nullable();
            $table->datetime('date_created')->nullable();
            $table->datetime('user_last_log')->nullable();
            $table->text('avatar_url')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->datetime('timestamp')->nullable();
            $table->unsignedInteger('user_type_id')->nullable();
        });

        $messages[] = "recreated 'users' table with a scalar user_type_id.";

        // 3. Seed data.
        // Order: id, first_name, last_name, email, country_id, region_id, city,
        // company_id, password, user_type_id, email_verified, status_id
        $usersData = [
            [1, 'Cat',  'Nduanya', 'mindofcat@hotmail.com',              39, 866, 'Barrie', null, '123xxx#A', 1, 1, 1],
            [2, 'Dale', 'Brown',   'dale@homeworksinspections.info',     39, 866, 'Angus',  null, '#HMWRKS#', 1, 1, 1],
            [3, 'Cat',  'Nduanya', 'catscriptapps@gmail.com',            39, 866, 'Barrie', 1,    '123xxx#A', 2, 1, 1],
            [4, 'Dale', 'Brown',   'homeworks.inspections.canada@gmail.com', 39, 866, 'Angus', 2, '#HMWRKS#', 2, 1, 1],
            [5, 'Cat',  'Nduanya', 'mindofcat@gmail.com',                39, 866, 'Barrie', 1,    '123xxx#A', 3, 1, 1],
            [6, 'Dale', 'Brown',   'homecomfortreports@gmail.com',       39, 866, 'Angus',  2,    '#HMWRKS#', 3, 1, 1],
        ];

        $count = 0;
        foreach ($usersData as $row) {
            User::create([
                'id'             => $row[0],
                'first_name'     => $row[1],
                'last_name'      => $row[2],
                'email'          => $row[3],
                'country_id'     => $row[4],
                'region_id'      => $row[5],
                'city'           => $row[6],
                'company_id'     => $row[7],
                'password'       => password_hash((string)$row[8], PASSWORD_DEFAULT),
                'user_type_id'   => $row[9],
                'email_verified' => (bool)$row[10],
                'status_id'      => $row[11],
                'date_created'   => date('Y-m-d'),
            ]);
            $count++;
        }

        $messages[] = "Successfully imported $count users.";
    } catch (\Throwable $e) {
        $messages[] = 'Users table error: ' . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}
