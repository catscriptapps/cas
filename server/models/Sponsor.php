<?php
// /server/models/Sponsor.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Logos shown on the public Sponsorship page. Legacy had these hardcoded in
 * PHP with no admin CRUD at all; this table exists specifically so an admin
 * can add/remove sponsors from the live site without a code deploy.
 */
class Sponsor extends Model
{
    protected $table = 'sponsors';
    protected $primaryKey = 'sponsor_id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    protected $fillable = [
        'name',
        'filename',
        'sort_order',
    ];

    protected $casts = [
        'sponsor_id'  => 'integer',
        'sort_order'  => 'integer',
        'date_created' => 'date',
        'timestamp'   => 'datetime',
    ];
}
