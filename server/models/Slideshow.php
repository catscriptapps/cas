<?php
// /server/models/Slideshow.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Homepage/global header slideshow images.
 * Table: slideshow_images
 */
class Slideshow extends Model
{
    protected $table = 'slideshow_images';

    protected $fillable = [
        'image_name',
        'pos_index',
        'status_id',
    ];

    protected $casts = [
        'id'         => 'integer',
        'pos_index'  => 'integer',
        'status_id'  => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function (Slideshow $slide) {
            $filePath = dirname(__DIR__, 2) . '/public/images/home/' . $slide->image_name;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        });
    }
}
