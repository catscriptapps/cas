<?php
// /server/models/NoteHint.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A reusable canned phrase a Company Admin can save from any notes-type
 * field on the inspection detail page, so it can be inserted again later
 * instead of retyping the same finding. Table: note_hints (see
 * scripts/reset/note-hints.php for the scoping rationale per type).
 */
class NoteHint extends Model
{
    protected $table = 'note_hints';

    const TYPE_INSPECTION_SUMMARY = 'inspection_summary';
    const TYPE_QUESTION_NOTES = 'question_notes';
    const TYPE_SECTION_COMMENT = 'section_comment';

    const TYPES = [
        self::TYPE_INSPECTION_SUMMARY,
        self::TYPE_QUESTION_NOTES,
        self::TYPE_SECTION_COMMENT,
    ];

    protected $fillable = [
        'company_id',
        'section_id',
        'type',
        'hint_text',
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

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
