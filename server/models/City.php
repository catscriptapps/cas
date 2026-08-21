<?php
// /server/models/City.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modernized City Model
 * Table: cities
 */
class City extends Model
{
    protected $table = 'cities';

    // Standardized to 'id'
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'city',
        'region_id'
    ];

    // Table doesn't have timestamps in the new SQL
    public $timestamps = false;

    /**
     * Relationship to the Region (Ontario, etc.)
     */
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }
}
