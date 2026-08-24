<?php
// /server/models/ContactRole.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactRole extends Model
{
    protected $table = 'contacts_roles';
    protected $primaryKey = 'id';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'role_slug',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'role_id', 'id');
    }
}
