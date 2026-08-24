<?php
// /scripts/reset/contacts.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Contact;

/**
 * Seeds the real contact directory carried over from legacy cas-sports
 * (league officials, timekeepers, and township/city contacts -- this
 * business's own operational data, migrated into the new schema). Legacy's
 * `status_id` column doubled as the role foreign key; here that value maps
 * to the new `role_id` column, and `status_id` is set to Active for every
 * migrated contact since legacy never actually used it to archive anyone.
 */
function resetContactsTable(): array
{
    $messages = [];
    $tableName = (new Contact())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('entry_id');
            $table->string('full_name', 300)->nullable();
            $table->string('organization', 300)->nullable();
            $table->string('email', 300)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('leagues', 300)->nullable();
            $table->integer('is_emergency')->nullable()->default(0);
            $table->unsignedInteger('role_id')->nullable();
            $table->integer('status_id')->nullable()->default(1);
            $table->date('date_created')->nullable();
            $table->dateTime('timestamp')->nullable();

            $table->index('role_id');
        });
        $messages[] = "fresh {$tableName} table created.";

        // Mapping: [entry_id, full_name, organization, email, phone, leagues, is_emergency, role_id, date_created, timestamp]
        $legacyContacts = [
            [120, 'Jack Birkett', '', 'jack8450@hotmail.com', '705-984-1617', 'All', 0, 2, '2023-02-02', '2023-02-02 12:47:07'],
            [119, 'Zach Capon', '', 'zacharycapon@rogers.com', '289-992-9698', 'All', 0, 3, '2022-12-19', '2022-12-19 15:38:42'],
            [103, 'Evan Connell', '', 'evanconnell9@icloud.com', '249-288-3488', 'All', 0, 1, '2022-02-06', '2024-09-06 05:07:00'],
            [90, 'Martin O\'Grady', '', 'martin.ogrady45@gmail.com', '705-718-0999', 'All', 0, 3, '2019-10-16', '2020-10-26 12:03:02'],
            [72, 'Scott Olesen', '', 'scott.olesen1@gmail.com', '705-716-2160', 'All', 0, 3, '2018-11-28', '2019-10-02 09:26:10'],
            [6, 'Colin Metcalfe', '', 'colinmetcalfe22@gmail.com', '705-970-3272', 'All', 0, 1, '2018-08-17', '2019-10-02 09:24:34'],
            [8, 'Cory Clapperton', '', 'cory.clapperton@gmail.com', '705-229-4202', 'All', 1, 1, '2018-08-17', '2018-08-17 16:03:32'],
            [114, 'Cooper Miller', '', 'coopergibsonmiller@icloud.com', '705-796-2673', 'All', 0, 1, '2022-10-16', '2022-10-16 17:51:43'],
            [10, 'Janelle Bernier', '', 'info@essahockey.com', '705-795-0232', 'All', 1, 1, '2018-08-17', '2018-08-17 16:03:32'],
            [11, 'Alicia Clapperton', '', 'aliciaclapperton@gmail.com', '705-229-4042', 'All', 0, 1, '2018-08-17', '2022-10-06 08:32:37'],
            [95, 'Jeremy Moore', '', 'jrlmoore@hotmail.com', '705-309-3219', 'Angus only', 1, 3, '2020-10-26', '2020-10-26 12:59:29'],
            [111, 'Max Gilman', '', 'info@essahockey.com', '705 795 0232', 'All', 1, 1, '2022-05-16', '2022-12-18 21:35:47'],
            [112, 'Mariah Hellmond', '', 'maddogmariah@gmail.com', '705-794-1994', 'Ball Hockey', 0, 1, '2022-05-16', '2022-05-16 08:14:33'],
            [116, 'Janice Welsh', '', 'janwelsh611@gmail.com', '705-734-7752', 'All', 0, 1, '2022-10-25', '2022-12-18 21:35:18'],
            [18, 'Chris Barnett', '', 'chrisbarnett0911@gmail.com', '705-241-1461', 'All', 0, 2, '2018-08-17', '2019-10-02 09:25:41'],
            [51, 'Chris Barnett', '', 'chrisbarnett0911@gmail.com', '705-241-1461', 'Womens', 0, 3, '2018-08-30', '2020-10-29 07:14:56'],
            [20, 'Cory Clapperton', '', 'cory.clapperton@gmail.com', '705-229-4202', 'All', 1, 2, '2018-08-17', '2018-08-17 16:03:32'],
            [21, 'Janelle Bernier', '', 'info@essahockey.com', '705-795-0232', 'Kids, Coed', 1, 2, '2018-08-17', '2018-08-17 16:03:32'],
            [23, 'Dave Slingerland', '', 'davidslingerland61@gmail.com', '705-791-7248', 'All', 1, 2, '2018-08-17', '2019-10-02 09:26:00'],
            [101, 'Keith Bonnyman', '', 'kbonnyman@hotmail.com', '905-890-4332', 'Womens', 0, 3, '2020-12-05', '2022-02-10 07:27:37'],
            [26, 'Dave Slingerland', '', 'davidslingerland61@gmail.com', '705-791-7248', 'All', 0, 3, '2018-08-17', '2022-09-15 14:53:52'],
            [118, 'Jeff Karn', '', 'jeffreykarn@gmail.com', '416-801-0443', 'All', 0, 3, '2022-12-19', '2022-12-19 07:10:49'],
            [100, 'Ryan & Colby Shaw', '', 'krisboy25@yahoo.ca', '249-535-1147', 'All', 0, 1, '2020-11-23', '2022-06-30 13:56:37'],
            [121, 'Alessandro Rocci', '', '', '416-575-1632', 'All', 0, 2, '2023-02-02', '2023-02-02 12:53:12'],
            [98, 'Ashford McCague', '', 'ashfordmccague4@gmail.com', '705-434-7575', 'ATB Womens', 0, 2, '2020-11-10', '2020-11-10 10:51:41'],
            [139, 'Tony Bezdeck', '', 'tonymazzei504@hotmail.com', '416-879-4466', 'All', 0, 2, '2023-09-13', '2023-09-13 18:35:00'],
            [33, 'Cory Clapperton', '', 'cory.clapperton@gmail.com', '705-229-4202', 'All', 1, 3, '2018-08-17', '2018-08-17 16:03:32'],
            [36, 'Shawn Gagne', '', 'xx_kewlguy_xx@hotmail.com', '705-795-3338', 'All', 0, 3, '2018-08-17', '2024-07-22 06:19:40'],
            [37, 'Marcie Battrick', 'City of Barrie - Rinks', 'Ice.Bookings@barrie.ca', '705-739-4220 x 4498', '', 0, 4, '2018-08-17', '2022-02-28 10:49:53'],
            [38, 'Jason Coleman', 'Essa Township', 'jcoleman@essatownship.on.ca', '705-424-9770 ext. 14', '', 0, 4, '2018-08-17', '2019-10-14 12:41:50'],
            [39, 'Ken Koopmans', 'Working Recreation Centre Manager/Parks', 'kkoopmans@essatownship.on.ca', '705-333-4127 (cell)', '', 0, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [40, 'Stuart Parkinson', 'Recreation Centre Supervisor (Thornton)', 'sparkinson@essatownship.on.ca', '705-623-0053(cell)', '', 0, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [41, 'Luke Boric', 'Innisfil Township', 'lboric@innisfil.ca', '705-436-3710 Ext. 43', '', 0, 4, '2018-08-17', '2019-10-14 12:40:53'],
            [42, 'Melissa Hatfield', 'New Tecumseth Township', 'MHatfield@newtecumseth.ca', '705-435-3900 Ext: 15', '', 0, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [43, 'Ken Koopmans', 'Essa (Angus) - Ball', 'kkoopmans@essatownship.on.ca', '705-333-4127 (cell)', '', 1, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [44, 'Stuart Parkinson', 'Essa (Thornton) - Ball', 'sparkinson@essatownship.on.ca', '705-623-0053(cell)', '', 1, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [45, 'See Location Attendant', 'Essa (Angus) - Ice', '', '', '', 1, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [46, 'See Location Attendant', 'Essa (Thornton) - Ice', '', '', '', 1, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [47, 'See Location Attendant', 'Barrie', '', '', '', 1, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [48, 'See Location Attendant', 'New Tecumseth', '', '', '', 1, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [49, 'See Location Attendant', 'Innisfil', '', '', '', 1, 4, '2018-08-17', '2018-08-17 16:03:32'],
            [117, 'Nathan Eveliegh', '', 'lisaverhagen@yahoo.ca', '705-500-7697', 'All', 0, 1, '2022-12-18', '2023-03-12 09:25:21'],
            [122, 'Derek Isidoro', '', 'derek_1191@hotmail.com', '906-806-1166', 'All', 0, 2, '2023-02-03', '2023-02-03 12:14:39'],
            [87, 'Kevin Dodds', '', 'kevin.dodds@hotmail.com', '705-816-6776', 'All', 0, 3, '2019-10-14', '2022-03-20 14:48:43'],
            [60, 'Edward Donato', '', 'edonato@smcdsb.on.ca', '705-795-8380', 'All', 0, 3, '2018-09-14', '2022-02-10 19:27:33'],
            [124, 'Wylie Birkett', '', 'wyliebirkett14@icloud.com', '705-791-7011', 'All', 0, 2, '2023-04-17', '2023-04-17 06:41:04'],
            [107, 'Bonnie Henry', '', 'tggbelle@gmail.com', '705-984-1883', 'All', 0, 1, '2022-03-12', '2022-05-26 13:51:58'],
            [110, 'Brian Oster', '', 'hockeydad99@outlook.com', '705-331-7735', 'All', 0, 2, '2022-05-13', '2023-10-13 13:03:01'],
            [108, 'Chris Ali', '', 'chrisali74@yahoo.ca', '416-317-1074', 'All', 0, 2, '2022-05-13', '2022-06-29 13:55:32'],
            [115, 'Kevin Kett', '', 'kkett27@hotmail.com', '705-984-3360', 'All', 0, 3, '2022-10-16', '2022-12-05 05:12:30'],
            [68, 'Wade Billotte', '', 'wade@wadewbillotte.com', '416-528-2423', 'All', 0, 3, '2018-10-08', '2022-02-10 19:17:37'],
            [106, 'Brian Oster', '', 'hockeydad99@outlook.com', '705-715-1269', 'All', 0, 3, '2022-02-28', '2025-01-01 13:58:17'],
            [96, 'Moe Guy', '', 'maguy@cokecanada.com', '705-331-6429', 'All', 0, 3, '2020-10-26', '2024-02-26 06:25:59'],
            [123, 'Matt Hackett', '', 'hackett_matt@hotmail.com', '705-794-7379', 'All', 0, 3, '2023-04-10', '2023-04-10 13:29:06'],
            [81, 'Merrick Iles', '', 'merrick_m_i@yahoo.ca', '705-500-6005', 'All', 0, 2, '2019-04-12', '2019-10-02 09:25:54'],
            [99, 'Kris Lemay', '', 'busbubbles1@hotmail.com', '705-435-8336', 'ATB Womens', 0, 1, '2020-11-10', '2020-11-10 10:52:19'],
            [125, 'Donny Fuller', '', 'dcoulsonfuller@hotmail.com', '', 'All', 0, 3, '2023-04-30', '2023-04-30 11:58:01'],
            [127, 'Jason Gillespie', '', 'jason4146@outlook.com', '', 'All', 0, 1, '2023-04-30', '2023-04-30 12:08:01'],
            [137, 'Ryan & Owen Mainprize', '', 'mainprizefam4@gmail.com', '', 'All', 0, 1, '2023-06-01', '2023-06-01 10:51:32'],
            [129, 'Jamie Tilley', '', 'jamietilley@bell.net', '(705) 770 - 0002', 'All', 0, 2, '2023-05-10', '2023-05-10 12:13:45'],
            [130, 'Matthew Tilley', '', 'matthewtilley12@yahoo.ca', '(705) 735 - 3181', 'All', 0, 2, '2023-05-10', '2023-05-10 12:14:24'],
            [138, 'Brian Mitchell', '', 'brianmitchell87@hotail.com', '705-619-2000', 'All', 0, 2, '2023-07-11', '2023-07-11 11:23:19'],
            [132, 'Carolin Cillis', '', 'ccillis67@gmail.com', '705-737-7074', 'All', 0, 1, '2023-05-10', '2023-08-02 08:36:05'],
            [144, 'Jake Denes', '', 'denesfamily5@gmail.com', '(705) 627 - 8383', 'All', 0, 2, '2024-10-01', '2024-10-01 11:05:19'],
            [143, 'Mike Zecchino', '', 'mzecchino2727@gmail.com', '705-791-1232', '', 0, 3, '2024-07-22', '2024-07-22 06:18:38'],
            [135, 'Ed Desjardine', '', 'johndesjardine@live.com', '', 'All', 0, 3, '2023-06-01', '2023-06-01 06:18:22'],
            [136, 'Hunter Godmere', '', 'huntergodmere7@gmail.com', '', 'All', 0, 3, '2023-06-01', '2023-06-01 15:05:27'],
            [140, 'Nick Makri', '', 'nickmakri87@gmail.com', '647-801-4711', 'All', 0, 3, '2024-01-10', '2024-01-10 12:31:22'],
            [141, 'Derek Rowles', '', 'derekrowles@gmail.com', '705-890-0378', 'All', 0, 3, '2024-02-26', '2024-02-26 19:34:38'],
            [142, 'Jenna Lee', '', 'jennanoelle72@live.ca', '705-391-8647', 'All', 0, 1, '2024-04-02', '2024-04-02 15:17:30'],
            [145, 'Larry Sutton', '', 'larrysutton88@gmail.com', '', 'All', 0, 3, '2024-10-20', '2024-10-20 18:48:39'],
            [146, 'Mike Stevanovic', '', 'stevanovicref@gmail.com', '', 'All', 0, 3, '2024-10-20', '2024-10-20 18:49:39'],
            [147, 'Olivia Barnett', '', 'oliviabarnett0929@gmail.com', '705-464-1090', 'All', 0, 1, '2024-11-11', '2024-11-11 14:33:01'],
            [148, 'Mackenzie Cuthbert', '', 'mcuthb92@gmail.com', '', 'All', 0, 3, '2024-11-13', '2024-11-13 19:57:31'],
            [149, 'Mark Shepherd', '', 'M_shepherd12@hotmail.com', '', 'All', 0, 2, '2025-09-02', '2025-09-02 16:05:10'],
        ];

        $data = [];
        foreach ($legacyContacts as $row) {
            $data[] = [
                'entry_id'     => $row[0],
                'full_name'    => $row[1] !== '' ? $row[1] : null,
                'organization' => $row[2] !== '' ? $row[2] : null,
                'email'        => $row[3] !== '' ? $row[3] : null,
                'phone'        => $row[4] !== '' ? $row[4] : null,
                'leagues'      => $row[5] !== '' ? $row[5] : 'All',
                'is_emergency' => $row[6],
                'role_id'      => $row[7],
                'status_id'    => 1,
                'date_created' => $row[8],
                'timestamp'    => $row[9],
            ];
        }

        if (!empty($data)) {
            Capsule::table($tableName)->insert($data);
            $messages[] = "seeded " . count($data) . " contacts from legacy payload.";
        }
    } catch (\Throwable $e) {
        $messages[] = "error resetting {$tableName}: " . $e->getMessage();
    }

    return $messages;
}
