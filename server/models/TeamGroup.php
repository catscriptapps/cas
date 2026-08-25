<?php
// /server/models/TeamGroup.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamGroup extends Model
{
    protected $table = 'teams_groups';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = ['group_name', 'sort_order'];

    protected $casts = [
        'id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'team_group', 'group_name');
    }
}
