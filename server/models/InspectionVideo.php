<?php
// /server/models/InspectionVideo.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * InspectionVideo Model
 * Table: inspection_videos
 *
 * One row per uploaded video, scoped to an inspection but NOT to any one
 * section -- the inspection's shared video library, uploaded via the
 * chunked video uploader. Made to appear in a section's grid by assigning
 * it via InspectionVideoSection (same library/assignment split as photos).
 */
class InspectionVideo extends Model
{
    protected $table = 'inspection_videos';

    protected $fillable = [
        'inspection_id',
        'filename',
        'date_created',
    ];

    protected $casts = [
        'inspection_id' => 'integer',
        'date_created'  => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null;

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }

    public function sectionLinks(): HasMany
    {
        return $this->hasMany(InspectionVideoSection::class, 'video_id', 'id');
    }
}
