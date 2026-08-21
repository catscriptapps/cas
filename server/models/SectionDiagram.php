<?php
// /server/models/SectionDiagram.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single diagram image for one Section of a company's question bank
 * (e.g. a furnace wiring schematic, a septic layout) -- stitched into every
 * finished PDF report for that company, immediately after that section's
 * photos, full-bleed like Cover Pages. Scoped by company_id + section_id
 * only; there's no inspection_id, since the same diagrams apply to every
 * report that section appears in, not a specific inspection.
 * Table: section_diagrams
 */
class SectionDiagram extends Model
{
    protected $table = 'section_diagrams';

    protected $fillable = [
        'company_id',
        'section_id',
        'image_name',
        'caption',
        'pos_index',
    ];

    protected $casts = [
        'id'         => 'integer',
        'company_id' => 'integer',
        'section_id' => 'integer',
        'pos_index'  => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function (SectionDiagram $diagram) {
            $filePath = dirname(__DIR__, 2) . '/public/images/uploads/section-diagrams/'
                . $diagram->company_id . '/' . $diagram->section_id . '/' . $diagram->image_name;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
