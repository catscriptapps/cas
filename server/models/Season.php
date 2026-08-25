<?php
// /server/models/Season.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A Season is one division's roster/schedule cycle for a given year -- the
 * actual unit the "Schedules" page lists (mirrors legacy cas-sports exactly:
 * Division -> Season(year) -> Team -> Player, and Season -> Schedule/game).
 */
class Season extends Model
{
    protected $table = 'seasons';
    protected $primaryKey = 'season_id';
    public $incrementing = true;
    public $timestamps = false;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'season_id',
        'division_id',
        'season_year',
        'status_id',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'season_id' => 'integer',
        'division_id' => 'integer',
        'status_id' => 'integer',
        'date_created' => 'date',
        'timestamp' => 'datetime',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'season_id', 'season_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'season_id', 'season_id');
    }
}
