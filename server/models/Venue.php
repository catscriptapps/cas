<?php
// /server/models/Venue.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rinks/arenas shown on the public "Locations" page, grouped by `sport`
 * ('ball' or 'ice'). Ported from a hardcoded array in resources/views/pages/
 * locations.php into a real, admin-manageable table (add/edit/delete,
 * reordered independently within each sport group) -- see VenuesController.
 *
 * Named Venue (table `venues`), not Location, because `Location`/`locations`
 * already exists in this app as an unrelated short-code lookup for the
 * Schedules form's rink dropdown (see server/models/Location.php).
 */
class Venue extends Model
{
    protected $table = 'venues';
    protected $primaryKey = 'venue_id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    const SPORT_BALL = 'ball';
    const SPORT_ICE = 'ice';

    protected $fillable = [
        'name',
        'address',
        'directions',
        'sport',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'venue_id'    => 'integer',
        'sort_order'  => 'integer',
        'date_created' => 'date',
        'timestamp'   => 'datetime',
    ];
}
