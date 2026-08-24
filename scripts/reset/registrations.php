<?php
// /scripts/reset/registrations.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Division;
use App\Models\Registration;
use App\Models\Source;

/**
 * Schema intentionally mirrors the legacy cas-sports `registrations` table
 * field-for-field (full_name as one column, age as a string, province_id,
 * position, has_paid as a plain int) -- see server/models/Registration.php
 * for why `paypal_order_id` is the one addition beyond that set.
 */
function resetRegistrationsTable(): array
{
    $messages = [];
    $tableName = 'registrations';

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->unsignedInteger('division_id')->nullable()->index();
            $table->string('full_name', 300)->nullable();
            $table->string('age', 30)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('city', 300)->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 300)->nullable()->index();
            $table->string('position', 200)->nullable();
            $table->unsignedInteger('hear_about_us')->nullable();
            $table->string('team_name', 200)->nullable();
            $table->text('special_requests')->nullable();

            // Payment -- set automatically by the PayPal capture/webhook flow,
            // or manually by an admin (e.g. e-transfer/cash registrations).
            $table->integer('has_paid')->nullable()->default(0)->index();
            $table->double('amount_paid')->nullable();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->string('paypal_order_id', 64)->nullable(); // see class docblock

            // Record status -- Active vs Archived, independent of payment.
            $table->integer('status_id')->nullable()->default(1)->index();

            $table->date('date_created')->nullable();
            $table->datetime('timestamp')->nullable();

            // Unused in the legacy source too -- kept only for field parity.
            $table->integer('registration_id')->nullable();
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

    // A realistic spread of special-requests notes, short and long, so the
    // admin table's Heard About Us / Special Requests column (now shown in
    // full, never truncated -- see resources/views/components/registrations/
    // data-row.php) has real content to wrap against instead of nothing.
    $specialRequestPool = [
        'Please try to place me on the same team as my brother, Jake Thompson -- we always play together.',
        'I have a peanut allergy. EpiPen will be in my bag at every game, just wanted the team to be aware.',
        'Requesting Tuesday or Thursday evening games only if at all possible -- I work weekends and can\'t make daytime slots.',
        'First time playing organized hockey in about 10 years, so please be gentle with the team placement! Happy to play any position.',
        'My daughter uses a hearing aid -- please let the coach know so they can get her attention visually during drills if needed.',
        'Carpooling with the Reynolds family from Angus, so please try to keep us on the same team if that\'s workable.',
        'I\'m recovering from a knee surgery earlier this year and would prefer a lower-contact division if there\'s flexibility.',
        'Would love to be placed with my usual linemates from last season if the roster allows -- we had great chemistry.',
        'Vegetarian if there are any post-game team meals or pizza nights, just a heads up for planning.',
        'Available for evening games only, no early mornings please -- I have a long commute to work.',
        'This is my son\'s first season ever. He\'s a bit nervous, so a patient, encouraging coach would mean a lot to our family.',
        'Please note I go by "Robbie" not my legal first name on the roster and jersey if possible.',
        'I coach my daughter\'s team on weekends so I need to avoid any Saturday morning conflicts with this registration.',
        'No allergies or medical concerns, just wanted to say we\'re really excited for the season to start!',
        'Would appreciate being kept off the same team as my younger brother -- we get too competitive with each other on the ice.',
        'I use a mobility aid off the ice and may need a few extra minutes getting to and from the bench -- totally fine to play, just a heads up.',
        'Please reach out to my mom (same phone number on file) for anything related to scheduling, not directly to me.',
        'Returning player, was on the Barrie team two seasons ago and would love to be reunited with that group if at all possible this year.',
        'Diabetic -- I carry my own supplies and I\'m self-sufficient, just flagging it in case of an emergency during a game.',
        'We just moved to the area from Ottawa, so this is our first season with the league -- looking forward to meeting everyone!',
        'Please avoid scheduling us against the Thornton Ice division if possible, my other child plays on that team and we\'d love to actually watch her games too.',
        'I\'m a bit taller than most kids my age so please don\'t let that push me into an older division -- I\'m still only 9!',
        'Happy to volunteer as an assistant coach or team parent if that\'s ever needed, just let me know who to contact.',
        'No special requests, just really looking forward to a great season!',
    ];

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

        // Age range: explicit "X to Y yr(s)/years" wins; then 35+/50+; else adult default.
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
            $specialRequests = random_int(1, 100) <= 45
                ? $specialRequestPool[array_rand($specialRequestPool)]
                : null;
            $emailCounter++;

            Registration::create([
                'division_id' => $division->division_id,
                'full_name' => "{$firstName} {$lastName}",
                'age' => (string)$age,
                'email' => strtolower("{$firstName}.{$lastName}{$emailCounter}@example.com"),
                'phone' => sprintf('705-555-%04d', random_int(0, 9999)),
                'address' => random_int(1, 999) . ' ' . $streets[array_rand($streets)],
                'city' => $cities[array_rand($cities)],
                'province_id' => $ontarioRegionId,
                'postal_code' => $postalPrefixes[array_rand($postalPrefixes)] . ' ' . random_int(0, 9) . chr(random_int(65, 90)) . random_int(0, 9),
                'position' => $positions[array_rand($positions)],
                'hear_about_us' => $sourceIds[array_rand($sourceIds)],
                'team_name' => null, // left unassigned for future team-building
                'special_requests' => $specialRequests,
                'has_paid' => $hasPaid ? 1 : 0,
                'amount_paid' => $hasPaid ? (float)$division->price : 0,
                'status_id' => Registration::STATUS_ACTIVE,
                'date_created' => date('Y-m-d', strtotime('-' . random_int(0, 45) . ' days')),
            ]);

            $totalSeeded++;
        }
    }

    return $totalSeeded;
}
