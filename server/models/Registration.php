<?php
// /server/models/Registration.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $table = 'registrations';
    protected $primaryKey = 'entry_id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // Record status -- Active vs Archived (independent of payment status).
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'division_id',
        'first_name',
        'last_name',
        'age',
        'email',
        'phone',
        'address',
        'city',
        'region_id',
        'postal_code',
        'desired_position',
        'hear_about_us',
        'team_name',
        'special_requests',
        'has_paid',
        'amount_paid',
        'paypal_order_id',
        'transaction_id',
        'status_id',
        'date_created',
    ];

    protected $casts = [
        'entry_id'    => 'integer',
        'division_id' => 'integer',
        'age'         => 'integer',
        'region_id'   => 'integer',
        'hear_about_us' => 'integer',
        'has_paid'    => 'boolean',
        'amount_paid' => 'decimal:2',
        'status_id'   => 'integer',
        'date_created' => 'date',
        'timestamp'   => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'hear_about_us', 'entry_id');
    }
}
