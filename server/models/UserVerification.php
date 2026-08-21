<?php
// /server/models/UserVerification.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserVerification Model
 * Manages temporary tokens for guest registration and account activation.
 */
class UserVerification extends Model
{
    /** @var string Table name */
    protected $table = 'user_verifications';

    /** @var string Primary key */
    protected $primaryKey = 'email';

    /** @var bool Disable auto-incrementing as we use email as the key */
    public $incrementing = false;

    /** @var bool Only created_at is needed for expiration logic */
    public $timestamps = false;

    /** @var array Fillable attributes for mass assignment */
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    /**
     * Checks if a token has expired.
     * * @param int $minutes Expiration threshold (default 60)
     * @return bool
     */
    public function isExpired(int $minutes = 60): bool
    {
        // Fallback for null created_at just in case
        if (!$this->created_at) return true;

        $createdAt = strtotime($this->created_at);
        return (time() - $createdAt) > ($minutes * 60);
    }
}
