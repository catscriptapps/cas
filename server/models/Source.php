<?php
// /server/models/Source.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * "How did you hear about us?" lookup, referenced by Registration::hear_about_us.
 */
class Source extends Model
{
    protected $table = 'sources';
    protected $primaryKey = 'entry_id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'hear_about_us',
        'status_id',
        'display_order',
        'date_created',
    ];

    protected $casts = [
        'entry_id'      => 'integer',
        'status_id'     => 'integer',
        'display_order' => 'integer',
        'date_created'  => 'date',
    ];
}
