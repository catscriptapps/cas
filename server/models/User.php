<?php
// /server/models/User.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modernized User Model
 * Table: users
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Standardized primary key.
     * We map the legacy user_id to 'id' during our reset/migration scripts.
     */
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'country_id',
        'region_id',
        'city',
        'password',
        'api_token',
        'status_id',
        'date_created',
        'user_last_log',
        'avatar_url',
        'email_verified',
        'timestamp',
        'user_type_id',
    ];

    /**
     * Attribute casting for automated JSON handling and Date objects.
     */
    protected $casts = [
        'id'             => 'integer',
        'country_id'     => 'integer',
        'region_id'      => 'integer',
        'status_id'      => 'integer',
        'email_verified' => 'boolean',
        'date_created'   => 'datetime',
        'user_last_log'  => 'datetime',
        'timestamp'      => 'datetime',
        'user_type_id'   => 'integer',
    ];

    /**
     * Mapping legacy timestamp names to Eloquent defaults.
     */
    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // ============================================================
    // Relationships
    // ============================================================

    /**
     * Link to the Country model.
     * Maps users.country_id -> countries.id
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Link to the Region model.
     * Maps users.region_id -> regions.id
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    // ============================================================
    // Accessors & Logic
    // ============================================================

    /**
     * Virtual attribute for $user->full_name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Helper to check a user's single type/role. Admin = 1 (see UserType).
     */
    public function isType(int $typeId): bool
    {
        return (int)$this->user_type_id === $typeId;
    }
}
