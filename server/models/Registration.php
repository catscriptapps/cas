<?php
// /server/models/Registration.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Field set intentionally mirrors the legacy cas-sports `registrations`
 * table exactly (full_name as one column, age stored as a string,
 * province_id, position, has_paid as a plain int) -- the only addition is
 * `paypal_order_id`, needed by the real PayPal Orders API flow this project
 * has (legacy never had working payment processing to track an order id for).
 */
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
        'full_name',
        'age',
        'address',
        'city',
        'province_id',
        'postal_code',
        'phone',
        'email',
        'position',
        'hear_about_us',
        'team_name',
        'special_requests',
        'has_paid',
        'amount_paid',
        'status_id',
        'registration_id',
        'transaction_id',
        'paypal_order_id',
        'date_created',
    ];

    protected $casts = [
        'entry_id'      => 'integer',
        'division_id'   => 'integer',
        'province_id'   => 'integer',
        'hear_about_us' => 'integer',
        'has_paid'      => 'integer',
        'amount_paid'   => 'float',
        'status_id'     => 'integer',
        'registration_id' => 'integer',
        'date_created'  => 'date',
        'timestamp'     => 'datetime',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'province_id', 'id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'hear_about_us', 'entry_id');
    }
}
