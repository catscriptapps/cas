<?php
// /server/models/InspectionPictureSection.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InspectionPictureSection Model
 * Table: inspection_picture_sections
 *
 * Junction row: assigns one library InspectionPicture to one Section on one
 * Inspection, with its own per-section caption and position (a photo can
 * appear in several sections at once, each with independent ordering/caption).
 */
class InspectionPictureSection extends Model
{
    protected $table = 'inspection_picture_sections';

    protected $fillable = [
        'inspection_id',
        'picture_id',
        'section_id',
        'description',
        'pos_index',
        'date_created',
    ];

    protected $casts = [
        'inspection_id' => 'integer',
        'picture_id'    => 'integer',
        'section_id'    => 'integer',
        'pos_index'     => 'integer',
        'date_created'  => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null;

    public function picture(): BelongsTo
    {
        return $this->belongsTo(InspectionPicture::class, 'picture_id', 'id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
