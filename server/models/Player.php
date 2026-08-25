<?php
// /server/models/Player.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A roster slot -- (season_id, team_id, user_id, player_number, is_goalie).
 * Has no name of its own; display name always comes through `profile`
 * (the Registration the player is).
 */
class Player extends Model
{
    protected $table = 'players';
    protected $primaryKey = 'player_id';
    public $incrementing = true;
    public $timestamps = false;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'player_id',
        'season_id',
        'team_id',
        'user_id',
        'player_number',
        'is_goalie',
        'status_id',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'player_id' => 'integer',
        'season_id' => 'integer',
        'team_id' => 'integer',
        'user_id' => 'integer',
        'is_goalie' => 'integer',
        'status_id' => 'integer',
        'date_created' => 'date',
        'timestamp' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'user_id', 'entry_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function stats(): HasMany
    {
        return $this->hasMany(PlayerStat::class, 'player_id', 'player_id');
    }
}
