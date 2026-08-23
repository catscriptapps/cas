<?php
// /server/models/Sport.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    protected $table = 'sports';
    protected $primaryKey = 'sport_id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'sport_name',
        'status_id',
    ];

    protected $casts = [
        'sport_id'   => 'integer',
        'status_id'  => 'integer',
        'date_created' => 'date',
        'timestamp'  => 'datetime',
    ];

    public function leagues(): HasMany
    {
        return $this->hasMany(League::class, 'sport_id', 'sport_id');
    }
}
