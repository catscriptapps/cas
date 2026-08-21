<?php
// /server/models/Question.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Question Model
 * Table: questions
 *
 * A checklist item within a Section. Carries either (or both) a set of
 * selectable answer options and fill-in-the-blank fields, filled in by an
 * inspector when it's actually used on a report.
 */
class Question extends Model
{
    protected $table = 'questions';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'company_id',
        'section_id',
        'question_title',
        'answer_mode',
        'is_condition',
        'pos_index',
        'status_id',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'id'             => 'integer',
        'company_id'     => 'integer',
        'section_id'     => 'integer',
        'answer_mode'    => 'integer',
        'is_condition'   => 'boolean',
        'pos_index'      => 'integer',
        'status_id'      => 'integer',
        'date_created'   => 'datetime',
        'timestamp'      => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // Observe & Report: the inspector notes the condition without acting on it.
    const MODE_OBSERVE = 0;
    // Perform Tasks: the inspector actively checks/tests the item.
    const MODE_PERFORM = 1;

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id', 'id')->orderBy('pos_index');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(QuestionField::class, 'question_id', 'id')->orderBy('pos_index');
    }
}
