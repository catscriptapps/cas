<?php
// /server/models/Location.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'location_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = ['location_id', 'location_desc'];

    protected $casts = [
        'location_id' => 'integer',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'location', 'location_id');
    }
}
