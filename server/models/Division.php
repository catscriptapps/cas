<?php
// /server/models/Division.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    protected $table = 'divisions';
    protected $primaryKey = 'division_id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'league_id',
        'division',
        'price',
        'status_id',
    ];

    protected $casts = [
        'division_id' => 'integer',
        'league_id'   => 'integer',
        'price'       => 'decimal:2',
        'status_id'   => 'integer',
        'date_created' => 'date',
        'timestamp'   => 'datetime',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class, 'league_id', 'league_id');
    }

    /**
     * Divisions whose parent league is active -- a division under an
     * archived league shouldn't be selectable for registration or shown in
     * the League Management Divisions tab, even if the division row itself
     * is still marked Active.
     */
    public function scopeActiveLeagues(Builder $query): Builder
    {
        return $query->whereHas('league', fn($q) => $q->where('status_id', League::STATUS_ACTIVE));
    }
}
