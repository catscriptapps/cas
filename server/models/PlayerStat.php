<?php
// /server/models/PlayerStat.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A player's season goal/assist/point totals -- one row per (player_id,
 * season_id), maintained by hand via admin-editable inputs on the Stats
 * page. goals_against/shots_on_goal are the goalie-only fields (GAA is
 * computed on read, goals_against/games_played, never stored). Independent
 * of the gamesheets table -- see Stat model docblock.
 */
class PlayerStat extends Model
{
    protected $table = 'player_stats';
    protected $primaryKey = 'entry_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'season_id',
        'team_id',
        'player_id',
        'goals',
        'assists',
        'points',
        'games_played',
        'goals_against',
        'shots_on_goal',
        'timestamp',
    ];

    protected $casts = [
        'entry_id' => 'integer',
        'season_id' => 'integer',
        'team_id' => 'integer',
        'player_id' => 'integer',
        'goals' => 'integer',
        'assists' => 'integer',
        'points' => 'integer',
        'games_played' => 'integer',
        'goals_against' => 'integer',
        'shots_on_goal' => 'integer',
        'timestamp' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'player_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }

    public function getPointsAttribute($value): int
    {
        return $value !== null ? (int)$value : ((int)($this->attributes['goals'] ?? 0) + (int)($this->attributes['assists'] ?? 0));
    }
}
