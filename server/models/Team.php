<?php
// /server/models/Team.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $table = 'teams';
    protected $primaryKey = 'team_id';
    public $incrementing = true;
    public $timestamps = false;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'team_id',
        'season_id',
        'team_number',
        'team_name',
        'team_rep_id',
        'contact',
        'team_coach',
        'status_id',
        'date_created',
        'timestamp',
        'team_group',
    ];

    protected $casts = [
        'team_id' => 'integer',
        'season_id' => 'integer',
        'team_rep_id' => 'integer',
        'status_id' => 'integer',
        'date_created' => 'date',
        'timestamp' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TeamGroup::class, 'team_group', 'group_name');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'team_id', 'team_id');
    }

    /**
     * team_rep_id points at a Registration (the registrant who signed up as
     * this team's contact person), not a User. Legacy cas-sports never
     * actually defines this relation despite referencing `->representative`
     * in two controllers, so the "Team Rep" column silently always renders
     * "N/A" there even though the underlying team_rep_id data is correct --
     * defining it for real here is a deliberate fix, not a divergence in the
     * underlying data.
     */
    public function representative(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'team_rep_id', 'entry_id');
    }
}
