<?php
// /server/models/Slide.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The home page hero's rotating background images. Ported from a hardcoded
 * array in layout-header.php (there was no admin module for this at all --
 * see that file's own former docblock) into a real, admin-manageable table.
 *
 * `filename` stores the FULL path relative to /public (e.g.
 * "images/home/hero-1.png" for the original curated defaults, or
 * "images/uploads/slideshow/xxxx.png" for anything an admin adds later) so
 * rendering never has to guess which folder a given row's image lives in.
 * The two folders also get different reset-time treatment: images/home/ is
 * permanent and survives a DB reset, while images/uploads/ is purged on
 * every reset along with the rest of that folder's transient content -- see
 * SlideshowController::delete()'s path check, which only ever unlinks files
 * under images/uploads/slideshow/, never the permanent defaults.
 */
class Slide extends Model
{
    protected $table = 'slideshow';
    protected $primaryKey = 'slide_id';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    protected $fillable = [
        'filename',
        'sort_order',
    ];

    protected $casts = [
        'slide_id'    => 'integer',
        'sort_order'  => 'integer',
        'date_created' => 'date',
        'timestamp'   => 'datetime',
    ];
}
