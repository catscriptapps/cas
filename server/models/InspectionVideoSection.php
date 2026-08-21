<?php
// /server/models/InspectionVideoSection.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InspectionVideoSection Model
 * Table: inspection_video_sections
 *
 * Junction row: assigns one library InspectionVideo to one Section on one
 * Inspection, with its own per-section caption and position -- same shape
 * as InspectionPictureSection.
 */
class InspectionVideoSection extends Model
{
    protected $table = 'inspection_video_sections';

    protected $fillable = [
        'inspection_id',
        'video_id',
        'section_id',
        'description',
        'pos_index',
        'date_created',
    ];

    protected $casts = [
        'inspection_id' => 'integer',
        'video_id'      => 'integer',
        'section_id'    => 'integer',
        'pos_index'     => 'integer',
        'date_created'  => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null;

    public function video(): BelongsTo
    {
        return $this->belongsTo(InspectionVideo::class, 'video_id', 'id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
