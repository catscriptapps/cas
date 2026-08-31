<?php
// /server/models/League.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    protected $table = 'leagues';
    protected $primaryKey = 'league_id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'sport_id',
        'league',
        'is_ball',
        'status_id',
    ];

    protected $casts = [
        'league_id'  => 'integer',
        'sport_id'   => 'integer',
        'is_ball'    => 'boolean',
        'status_id'  => 'integer',
        'date_created' => 'date',
        'timestamp'  => 'datetime',
    ];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id', 'sport_id');
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class, 'league_id', 'league_id');
    }
}
