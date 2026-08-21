<?php
// /server/models/Company.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Company Model
 * Table: companies
 *
 * Represents a subscribing inspection company on the platform. Company
 * Admin users (see UserType::COMPANY_ADMIN) sign in and create reports on
 * behalf of the company they belong to.
 */
class Company extends Model
{
    protected $table = 'companies';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'company_name',
        'email',
        'phone',
        'toll_free',
        'website',
        'slogan',
        'address',
        'city',
        'country_id',
        'region_id',
        'postal_code',
        'logo_url',
        'general_summary',
        'status_id',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'id'           => 'integer',
        'country_id'   => 'integer',
        'region_id'    => 'integer',
        'status_id'    => 'integer',
        'date_created' => 'datetime',
        'timestamp'    => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /**
     * Users assigned to this company (Company Admins).
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }
}
