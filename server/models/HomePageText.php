<?php
// /server/models/HomePageText.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Admin-editable freeform HTML blocks shown on public pages. Ported from
 * legacy essahockey_live's `essa_home_page_text` table -- legacy actually
 * hardcodes 4 rows (Our Mission, What's New, and two League Details blurbs)
 * behind one shared admin edit form, but only entry_id 1 ("Our Mission",
 * the block between the home page's intro paragraph and its register
 * buttons) was asked for here, so only that row is ported.
 */
class HomePageText extends Model
{
    protected $table = 'home_page_text';
    protected $primaryKey = 'entry_id';

    public $incrementing = true;
    public $timestamps = false;

    const OUR_MISSION = 1;

    protected $fillable = [
        'entry_id',
        'text_content',
        'timestamp',
    ];

    protected $casts = [
        'entry_id' => 'integer',
        'timestamp' => 'datetime',
    ];
}
