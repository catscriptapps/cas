<?php
// /server/models/InspectionQuestionOption.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InspectionQuestionOption Model
 * Table: inspection_question_options
 *
 * A checked answer-option for a question on a given inspection. Existence
 * of a row = checked; unchecking deletes the row (same pattern as legacy's
 * hit_inspections_chk).
 */
class InspectionQuestionOption extends Model
{
    protected $table = 'inspection_question_options';

    public $timestamps = false;

    protected $fillable = [
        'inspection_id',
        'question_id',
        'question_option_id',
    ];

    protected $casts = [
        'inspection_id'       => 'integer',
        'question_id'         => 'integer',
        'question_option_id'  => 'integer',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id', 'id');
    }
}
