<?php
// /server/models/Notification.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modernized Notification Model
 * Table: notifications
 *
 * Recipients can be a tenant, landlord, or (staff) user — three separate
 * account tables — so recipients are addressed by recipient_type +
 * recipient_id rather than a single foreign key.
 */
class Notification extends Model
{
    protected $table = 'notifications';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'recipient_type', // 'tenant' | 'landlord' | 'user'
        'recipient_id',
        'type',           // e.g. 'application_status'
        'subject',
        'message',
        'link',           // where clicking the notification should navigate to
        'is_read',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'id'             => 'integer',
        'recipient_id'   => 'integer',
        'is_read'        => 'boolean',
        'date_created'   => 'datetime',
        'timestamp'      => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';
}
