<?php
// /server/models/QuestionField.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * QuestionField Model
 * Table: question_fields
 *
 * A fill-in-the-blank field belonging to a Question, e.g. prefix "Approx.
 * age:" + suffix "years" renders as "Approx. age: ___ years" on a report.
 */
class QuestionField extends Model
{
    protected $table = 'question_fields';
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'prefix',
        'suffix',
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
