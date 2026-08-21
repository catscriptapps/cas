<?php
// /server/models/Section.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Section Model
 * Table: sections
 *
 * A tab in a Company's question bank (Roof, Electrical, Plumbing, etc).
 * Scoped to a single company -- each company builds its own set.
 */
class Section extends Model
{
    protected $table = 'sections';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'company_id',
        'name',
        'icon',
        'pos_index',
        'status_id',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'id'           => 'integer',
        'company_id'   => 'integer',
        'pos_index'    => 'integer',
        'status_id'    => 'integer',
        'date_created' => 'datetime',
        'timestamp'    => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'section_id', 'id')->orderBy('pos_index');
    }
}
