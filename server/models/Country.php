<?php
// /server/models/Country.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modernized Country Model
 * Table: countries
 */
class Country extends Model
{
    protected $table = 'countries';

    // Standardized to 'id' as per your new SQL
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'country',
        'country_code',
        'currency',
        'currency_symbol'
    ];

    // Disabling timestamps if the new table doesn't have created_at/updated_at
    public $timestamps = false;

    /**
     * Scope to find by code (e.g., CAN, USA)
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('country_code', strtoupper($code));
    }
}
