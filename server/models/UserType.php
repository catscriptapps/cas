<?php
// /server/models/UserType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent UserType model
 * Represents the users_types table
 */
class UserType extends Model
{
    protected $table = 'users_types';

    // Legacy table uses user_type_id as the primary key
    protected $primaryKey = 'user_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_type', // e.g., 'Realtor', 'Contractor', 'Individual'
    ];

    /**
     * Disable timestamps if the legacy table doesn't have 
     * created_at/updated_at columns.
     */
    public $timestamps = false;

    // ============================================================
    // Type Constants (For Logic Checks)
    // ============================================================
    const ADMIN = 1;

    // ============================================================
    // Relationships
    // ============================================================

    /**
     * Get all users assigned to this type.
     * * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_type_id', 'user_type_id');
    }
}
