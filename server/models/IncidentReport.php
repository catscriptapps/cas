<?php
// /server/models/IncidentReport.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * On-floor incident log (altercations, injuries, disciplinary outcomes).
 * Ported from legacy cas-sports -- a standalone administrative record with
 * no foreign keys to any other table. `teams_involved`, `persons_involved`,
 * `ref_involved`, `timekeeper`, and `manager_name` are all free-text fields
 * typed by whoever filed the report, not entity references.
 */
class IncidentReport extends Model
{
    protected $table = 'incident_reports';
    protected $primaryKey = 'entry_id';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'incident_date',
        'incident_time',
        'teams_involved',
        'persons_involved',
        'location',
        'ref_involved',
        'timekeeper',
        'incident',
        'equipment_worn',
        'medical_assistance',
        'manager_name',
        'manager_time',
        'referee_outcome',
        'name_e_signature',
        'status_id',
    ];

    protected $casts = [
        'entry_id' => 'integer',
        'incident_date' => 'date',
        'status_id' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status_id', 1);
    }
}
