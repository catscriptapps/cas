<?php
// /server/models/InspectionPicture.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * InspectionPicture Model
 * Table: inspection_pictures
 *
 * One row per uploaded photo, scoped to an inspection but NOT to any one
 * section -- this is the inspection's shared photo library. A photo is made
 * to appear in a section's grid (or the Contracts page) by assigning it via
 * InspectionPictureSection / InspectionPictureContract, the same photo can
 * be assigned to multiple sections at once. Mirrors legacy's
 * hit_inspections_pics + hit_inspections_pics_sections split.
 */
class InspectionPicture extends Model
{
    protected $table = 'inspection_pictures';

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
        return $this->hasMany(InspectionPictureSection::class, 'picture_id', 'id');
    }

    public function contractLink(): HasOne
    {
        return $this->hasOne(InspectionPictureContract::class, 'picture_id', 'id');
    }
}
