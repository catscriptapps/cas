<?php
// /server/models/InspectionQuestionField.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InspectionQuestionField Model
 * Table: inspection_question_fields
 *
 * The filled-in value for one of a question's fill-in-the-blank fields
 * (e.g. "Approx. age: [42] years") on a given inspection.
 */
class InspectionQuestionField extends Model
{
    protected $table = 'inspection_question_fields';

    public $timestamps = false;

    protected $fillable = [
        'inspection_id',
        'question_id',
        'question_field_id',
        'response_text',
    ];

    protected $casts = [
        'inspection_id'       => 'integer',
        'question_id'         => 'integer',
        'question_field_id'   => 'integer',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(QuestionField::class, 'question_field_id', 'id');
    }
}
