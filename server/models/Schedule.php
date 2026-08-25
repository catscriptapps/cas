<?php
// /server/models/Schedule.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single scheduled game. `game_time` is deliberately a free-text string
 * ("7:15 PM"), matching legacy cas-sports exactly -- sorting chronologically
 * requires STR_TO_DATE(game_time, '%l:%i %p') in the ORDER BY (see
 * SchedulesController), not a plain column sort.
 */
class Schedule extends Model
{
    protected $table = 'schedules';
    protected $primaryKey = 'entry_id';
    public $incrementing = true;
    public $timestamps = false;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'entry_id',
        'season_id',
        'game_date',
        'game_time',
        'location',
        'home',
        'away',
        'referee1',
        'referee2',
        'timekeep',
        'is_playoff',
        'status_id',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'entry_id' => 'integer',
        'season_id' => 'integer',
        'location' => 'integer',
        'home' => 'integer',
        'away' => 'integer',
        'is_playoff' => 'integer',
        'status_id' => 'integer',
        'game_date' => 'date',
        'timestamp' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home', 'team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away', 'team_id');
    }

    public function locationRelation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location', 'location_id');
    }
}
