<?php
// /server/models/InspectionSectionComment.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InspectionSectionComment Model
 * Table: inspection_section_comments
 *
 * One free-text comment box per section on a given inspection.
 */
class InspectionSectionComment extends Model
{
    protected $table = 'inspection_section_comments';

    public $timestamps = false;

    protected $fillable = [
        'inspection_id',
        'section_id',
        'comments',
    ];

    protected $casts = [
        'inspection_id' => 'integer',
        'section_id'    => 'integer',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
