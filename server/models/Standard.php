<?php
// /server/models/Standard.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One page of a company's Standards of Practice boilerplate -- free-form
 * rich-text HTML, no title/category/section grouping (mirrors legacy's
 * hit_standards_txts exactly in shape). Scoped by company_id only, same
 * "one document reused across every PDF report" pattern as CoverPage; when
 * PDF generation is built, each page's content gets printed verbatim into
 * its own page-break div, ordered by pos_index.
 * Table: standards
 */
class Standard extends Model
{
    protected $table = 'standards';

    protected $fillable = [
        'company_id',
        'content',
        'pos_index',
    ];

    protected $casts = [
        'id'         => 'integer',
        'company_id' => 'integer',
        'pos_index'  => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
