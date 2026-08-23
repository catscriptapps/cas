<?php
// /scripts/reset/registrations.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Division;
use App\Models\Registration;
use App\Models\Source;

function resetRegistrationsTable(): array
{
    $messages = [];
    $tableName = 'registrations';

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->unsignedInteger('division_id')->nullable()->index();

            $table->string('first_name', 150);
            $table->string('last_name', 150);
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('email', 300)->index();
            $table->string('phone', 20)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('city', 150)->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('desired_position', 200)->nullable();
            $table->unsignedInteger('hear_about_us')->nullable();
            $table->string('team_name', 200)->nullable();
            $table->text('special_requests')->nullable();

            // Payment -- set automatically by the PayPal capture/webhook flow,
            // or manually by an admin (e.g. e-transfer/cash registrations).
            $table->boolean('has_paid')->default(false)->index();
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->string('paypal_order_id', 64)->nullable();
            $table->string('transaction_id', 64)->nullable()->index();

            // Record status -- Active vs Archived, independent of payment.
            $table->integer('status_id')->default(1)->index();

            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });
        $messages[] = "created '{$tableName}' table structure.";

        $count = seedDemoRegistrations();
        $messages[] = "seeded {$count} demo registrations across all active divisions.";
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}

/**
 * Fills every active division with a plausible-looking roster of
 * registrants (fictional -- no real people), so there's a real player pool
 * to build team assignment against before that feature exists. Age range
 * and first-name pool are inferred from the division's own name (e.g. "U10
 * (7 to 9 yrs)", "Mens 35+", "Womens") so a kids' division doesn't end up
 * with 40-year-olds in it.
 */
function seedDemoRegistrations(): int
{
    $maleFirstNames = ['James', 'Michael', 'Robert', 'John', 'David', 'Chris', 'Matthew', 'Kevin', 'Brian', 'Steve', 'Mark', 'Paul', 'Andrew', 'Joshua', 'Tyler', 'Ryan', 'Jake', 'Daniel', 'Eric', 'Nicholas', 'Sam', 'Adam', 'Justin', 'Brad', 'Cody', 'Shawn', 'Craig', 'Greg', 'Scott', 'Tom', 'Jason', 'Aaron', 'Derek', 'Jordan', 'Ben'];
    $femaleFirstNames = ['Jennifer', 'Lisa', 'Michelle', 'Amanda', 'Ashley', 'Sarah', 'Emily', 'Jessica', 'Amy', 'Nicole', 'Laura', 'Stephanie', 'Rebecca', 'Megan', 'Kayla', 'Erin', 'Danielle', 'Katie', 'Rachel', 'Samantha', 'Emma', 'Olivia', 'Chloe', 'Hannah', 'Natalie', 'Victoria', 'Alicia', 'Christina', 'Melissa', 'Kim', 'Taylor', 'Sydney', 'Brooke', 'Paige', 'Julia'];
    $kidFirstNames = ['Ethan', 'Liam', 'Noah', 'Mason', 'Logan', 'Lucas', 'Jack', 'Owen', 'Carter', 'Wyatt', 'Ava', 'Sophia', 'Mia', 'Isabella', 'Charlotte', 'Amelia', 'Ella', 'Grace', 'Lily', 'Zoe'];
    $lastNames = ['Smith', 'Johnson', 'Brown', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott', 'Green', 'Baker', 'Adams', 'Nelson', 'Carter', 'Mitchell', 'Roberts', 'Turner', 'Phillips', 'Campbell', 'Parker', 'Evans', 'Edwards', 'Collins', 'Stewart'];
    $cities = ['Barrie', 'Angus', 'Thornton', 'Midhurst', 'Innisfil', 'Orillia', 'Alliston', 'Bradford', 'New Tecumseth', 'Wasaga Beach', 'Springwater', 'Oro-Medonte'];
    $streets = ['Main St', 'King St', 'Queen St', 'Victoria St', 'Yonge St', 'Bayfield St', 'Dunlop St', 'Grove St', 'Essa Rd', 'Bradford St', 'Anne St', 'Cedar Pointe Dr'];
    $positions = ['Forward', 'Defense', 'Goalie', 'Center', 'Left Wing', 'Right Wing'];
    $postalPrefixes = ['L4N', 'L4M', 'L9X', 'L0L', 'L3V', 'L0M'];

    $sourceIds = Source::pluck('entry_id')->all();
    if (empty($sourceIds)) {
        $sourceIds = [null];
    }

    $ontarioRegionId = 866; // matches scripts/reset/regions.php

    $divisions = Division::where('status_id', Division::STATUS_ACTIVE)->get();
    $emailCounter = 0;
    $totalSeeded = 0;

    foreach ($divisions as $division) {
        $name = $division->division;

        // Age range: explicit "X to Y yr(s)" wins; then 35+/50+; else adult default.
        if (preg_match('/(\d+)\s*to\s*(\d+)\s*(?:yrs?|years?)/i', $name, $m)) {
            [$minAge, $maxAge] = [(int)$m[1], (int)$m[2]];
        } elseif (str_contains($name, '50+')) {
            [$minAge, $maxAge] = [50, 65];
        } elseif (str_contains($name, '35+')) {
            [$minAge, $maxAge] = [35, 55];
        } else {
            [$minAge, $maxAge] = [18, 45];
        }

        $isKids = $maxAge <= 17;

        // Name pool: explicit Men's/Women's division narrows it; otherwise
        // (Co-Ed, kids, unisex leagues) draw from a mixed pool.
        if (!$isKids && preg_match('/\bwomen/i', $name)) {
            $namePool = $femaleFirstNames;
        } elseif (!$isKids && preg_match('/\bmen/i', $name)) {
            $namePool = $maleFirstNames;
        } elseif ($isKids) {
            $namePool = $kidFirstNames;
        } else {
            $namePool = array_merge($maleFirstNames, $femaleFirstNames);
        }

        // Bigger rosters for adult divisions (enough for several teams),
        // smaller for kids' divisions.
        $rosterSize = $isKids ? random_int(8, 12) : random_int(12, 18);

        for ($i = 0; $i < $rosterSize; $i++) {
            $firstName = $namePool[array_rand($namePool)];
            $lastName = $lastNames[array_rand($lastNames)];
            $age = random_int($minAge, $maxAge);
            $hasPaid = random_int(1, 100) <= 65; // ~65% already paid
            $emailCounter++;

            Registration::create([
                'division_id' => $division->division_id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'age' => $age,
                'email' => strtolower("{$firstName}.{$lastName}{$emailCounter}@example.com"),
                'phone' => sprintf('705-555-%04d', random_int(0, 9999)),
                'address' => random_int(1, 999) . ' ' . $streets[array_rand($streets)],
                'city' => $cities[array_rand($cities)],
                'region_id' => $ontarioRegionId,
                'postal_code' => $postalPrefixes[array_rand($postalPrefixes)] . ' ' . random_int(0, 9) . chr(random_int(65, 90)) . random_int(0, 9),
                'desired_position' => $positions[array_rand($positions)],
                'hear_about_us' => $sourceIds[array_rand($sourceIds)],
                'team_name' => null, // left unassigned for future team-building
                'special_requests' => null,
                'has_paid' => $hasPaid,
                'amount_paid' => $hasPaid ? $division->price : 0,
                'status_id' => Registration::STATUS_ACTIVE,
                'date_created' => date('Y-m-d', strtotime('-' . random_int(0, 45) . ' days')),
            ]);

            $totalSeeded++;
        }
    }

    return $totalSeeded;
}
