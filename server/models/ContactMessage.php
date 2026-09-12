<?php
// /server/models/ContactMessage.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A submission from the public "Contact Us" form (see server/api/contact.php).
 * Previously these were only ever emailed out with no record kept anywhere
 * (see that file's old comment); this table is the admin-facing inbox for
 * them -- list/view/archive/delete via MessagesController, surfaced through
 * the topbar's Messages icon (admin-only) and its unread-count badge.
 */
class ContactMessage extends Model
{
    protected $table = 'contact_messages';
    protected $primaryKey = 'entry_id';

    public $incrementing = true;

    // status_id follows the same 1/0 "Inbox vs Archived" convention as
    // Registrations/IncidentReports/Contacts elsewhere in this app, rather
    // than a separate is_archived column.
    public const STATUS_INBOX = 1;
    public const STATUS_ARCHIVED = 0;

    protected $fillable = [
        'full_name',
        'email',
        'subject',
        'message',
        'is_read',
        'status_id',
    ];

    protected $casts = [
        'entry_id' => 'integer',
        'is_read' => 'boolean',
        'status_id' => 'integer',
    ];

    /**
     * Drives the topbar's unread-count badge -- unread AND still in the
     * inbox (an archived-but-unread message no longer counts, since
     * archiving is how an admin marks something as "handled").
     */
    public static function unreadInboxCount(): int
    {
        return static::where('is_read', false)->where('status_id', self::STATUS_INBOX)->count();
    }
}
