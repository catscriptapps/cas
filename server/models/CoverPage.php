<?php
// /server/models/CoverPage.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single image in a company's Cover Pages gallery -- stitched onto the
 * front or back of every finished PDF report for that company (see PDF
 * generation, not yet built). Scoped by company_id only; there's no
 * inspection_id, since the same gallery applies to every report a company
 * generates, not a specific one.
 * Table: cover_pages
 */
class CoverPage extends Model
{
    protected $table = 'cover_pages';

    const LOCATION_FRONT = 1;
    const LOCATION_BACK = 2;

    protected $fillable = [
        'company_id',
        'image_name',
        'page_location',
        'pos_index',
    ];

    protected $casts = [
        'id'            => 'integer',
        'company_id'    => 'integer',
        'page_location' => 'integer',
        'pos_index'     => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function (CoverPage $coverPage) {
            $filePath = dirname(__DIR__, 2) . '/public/images/uploads/cover-pages/' . $coverPage->company_id . '/' . $coverPage->image_name;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
