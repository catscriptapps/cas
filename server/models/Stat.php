<?php
// /server/models/Stat.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A team's win/loss/tie/goals record for one season -- one row per
 * (team_id, season_id, is_playoff), maintained entirely by hand via
 * admin-editable inputs on the Stats page. There is deliberately no
 * game-outcome inference anywhere in this feature (see StatsController
 * docblock): schedules carries no score columns, and gamesheets' per-player
 * goals are never summed into a team total. Matches legacy cas-sports.
 */
class Stat extends Model
{
    protected $table = 'stats';
    protected $primaryKey = 'entry_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'season_id',
        'team_id',
        'wins',
        'losses',
        'ties',
        'goals_for',
        'goals_against',
        'is_playoff',
        'timestamp',
    ];

    protected $casts = [
        'entry_id' => 'integer',
        'season_id' => 'integer',
        'team_id' => 'integer',
        'wins' => 'integer',
        'losses' => 'integer',
        'ties' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'is_playoff' => 'integer',
        'timestamp' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }
}
