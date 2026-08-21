<?php
// /server/models/InspectionPictureContract.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InspectionPictureContract Model
 * Table: inspection_picture_contracts
 *
 * Junction row: assigns one library InspectionPicture to this inspection's
 * Contracts page. At most one row per picture (a photo is either a contract
 * document or it isn't -- unlike sections, there's only one "contracts"
 * target). Prints right before Section 1 in the PDF.
 */
class InspectionPictureContract extends Model
{
    protected $table = 'inspection_picture_contracts';

    protected $fillable = [
        'inspection_id',
        'picture_id',
        'description',
        'pos_index',
        'date_created',
    ];

    protected $casts = [
        'inspection_id' => 'integer',
        'picture_id'    => 'integer',
        'pos_index'     => 'integer',
        'date_created'  => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = null;

    public function picture(): BelongsTo
    {
        return $this->belongsTo(InspectionPicture::class, 'picture_id', 'id');
    }
}
