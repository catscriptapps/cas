<?php
// /server/models/QuestionOption.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * QuestionOption Model
 * Table: question_options
 *
 * A single selectable answer choice belonging to a Question (e.g.
 * "Satisfactory", "Repair/Replace", "Not Present").
 */
class QuestionOption extends Model
{
    protected $table = 'question_options';
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'option_text',
        'pos_index',
    ];

    protected $casts = [
        'id'          => 'integer',
        'question_id' => 'integer',
        'pos_index'   => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}
