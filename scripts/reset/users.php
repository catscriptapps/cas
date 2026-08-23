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

        // 2. Create structure -- there's currently only one backend role
        // (Admin), so user_type_id is a plain scalar column, not a collection.
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id'); // This is our BIGINT to match other tables
            $table->string('first_name', 300)->nullable();
            $table->string('last_name', 300)->nullable();
            $table->string('email', 300)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->string('city', 300)->nullable();
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

        // 3. Seed data -- the same default staff accounts as the legacy
        // cas_sports project (country_id 39 = Canada, region_id 866 = Ontario).
        // Order: id, first_name, last_name, email, country_id, region_id, city,
        // password, user_type_id, email_verified, status_id
        $usersData = [
            [1, 'Cat',     'Nduanya',     'mindofcat@hotmail.com', 39, 866, 'Barrie',   '123xxx#A', 1, 1, 1],
            [2, 'Janelle', 'Bernier',     'janelle@essahockey.com', 39, 866, 'Thornton', '123456#',  1, 1, 1],
            [3, 'Cory',    'Clapperton',  'cory@essahockey.com',    39, 866, 'Thornton', '123456#',  1, 1, 1],
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
                'password'       => password_hash((string)$row[7], PASSWORD_DEFAULT),
                'user_type_id'   => $row[8],
                'email_verified' => (bool)$row[9],
                'status_id'      => $row[10],
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
