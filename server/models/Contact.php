<?php
// /server/models/Contact.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * League officials, timekeepers, and township/city contacts -- modeled on
 * legacy cas-sports' contacts table, with one deliberate schema improvement:
 * legacy overloads `status_id` to double as the foreign key into
 * contacts_roles (a comment in its own model admits as much). Here `role_id`
 * is a real, separate column and `status_id` is a genuine active/archived
 * flag, matching how every other table in this project uses it. The vestigial
 * `password` column (unused by any legacy UI -- contacts never log in) is
 * dropped rather than carried forward.
 */
class Contact extends Model
{
    protected $table = 'contacts';
    protected $primaryKey = 'entry_id';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'full_name',
        'organization',
        'email',
        'phone',
        'leagues',
        'is_emergency',
        'role_id',
        'status_id',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'entry_id'     => 'integer',
        'is_emergency' => 'integer',
        'role_id'      => 'integer',
        'status_id'    => 'integer',
        'date_created' => 'date',
        'timestamp'    => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(ContactRole::class, 'role_id', 'id');
    }

    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status_id', self::STATUS_ACTIVE);
    }
}
