<?php
// /server/models/Faq.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $table = 'faqs';

    /**
     * Constants for status mapping
     */
    public const STATUS_ACTIVE = 1;
    public const STATUS_ARCHIVED = 2;

    protected $fillable = [
        'question',
        'answer',
        'status_id',     // 1 = Active/Current, 2 = Archived
        'display_order',
        'orig_user_id',
    ];

    protected $casts = [
        'status_id'     => 'integer',
        'display_order' => 'integer',
        'orig_user_id'  => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Scope to only include active FAQs for the public view
     */
    public function scopeActive($query)
    {
        return $query->where('status_id', self::STATUS_ACTIVE);
    }

    /**
     * Get the user who created this FAQ entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orig_user_id');
    }
}
