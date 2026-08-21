<?php
// /server/models/Message.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'conversation_id', // Group identifier
        'parent_id',       // Direct link to the message being replied to
        'full_name',    // Kept for guest contact forms
        'email',        // Kept for guest contact forms
        'subject',
        'message',
        'is_read',
        'is_sent',
        'is_draft',
        'is_archived'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_sent' => 'boolean',
        'is_draft' => 'boolean',
        'is_archived' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
