<?php
// /server/models/InspectionQuestion.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InspectionQuestion Model
 * Table: inspection_questions
 *
 * One row per question answered on an inspection: the Tasks status
 * (blank/n-a/Inspected/Not Inspected) and a free-text notes field.
 * Selected checkbox options and filled-in-blank field responses live in
 * their own sibling tables (InspectionQuestionOption / InspectionQuestionField).
 */
class InspectionQuestion extends Model
{
    protected $table = 'inspection_questions';

    public $timestamps = false;

    protected $fillable = [
        'inspection_id',
        'section_id',
        'question_id',
        'tasks',
        'notes',
    ];

    protected $casts = [
        'inspection_id' => 'integer',
        'section_id'    => 'integer',
        'question_id'   => 'integer',
        'tasks'         => 'integer',
    ];

    const TASK_BLANK         = 0;
    const TASK_NOT_APPLICABLE = 1;
    const TASK_INSPECTED     = 2;
    const TASK_NOT_INSPECTED = 3;

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}
