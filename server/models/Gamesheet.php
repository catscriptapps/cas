<?php
// /server/models/Gamesheet.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single player's stat line for a single game (goals/assists/games_played,
 * plus a first-goal period/time), one row per (schedule_id, player_id).
 * Entirely independent of the Stats/Standings feature's `stats`/`player_stats`
 * tables -- there is no aggregation from here into those, and no game-score
 * concept exists anywhere in this app (see StatsController's docblock).
 */
class Gamesheet extends Model
{
    protected $table = 'gamesheets';
    protected $primaryKey = 'entry_id';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'schedule_id',
        'season_id',
        'team_id',
        'player_id',
        'period',
        'time_of_goal',
        'goals',
        'assists',
        'games_played',
        'timestamp',
    ];

    protected $casts = [
        'entry_id' => 'integer',
        'schedule_id' => 'integer',
        'season_id' => 'integer',
        'team_id' => 'integer',
        'player_id' => 'integer',
        'period' => 'integer',
        'goals' => 'integer',
        'assists' => 'integer',
        'games_played' => 'integer',
        'timestamp' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'entry_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }
}
