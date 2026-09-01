<?php
// /server/models/RegistrantAccount.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A registrant's login credential -- decoupled from `registrations` itself
 * since one email can legitimately own several registration rows (a parent
 * registering more than one kid, or the same person registering across
 * multiple seasons). One password per email covers every registration under
 * it, rather than needing to keep a password column in sync across rows.
 *
 * `email` doubles as the primary key, matching `password_resets` (also
 * email-keyed) -- the existing password-reset flow works for a registrant
 * with zero schema changes to that table, see AuthController::resetPassword().
 */
class RegistrantAccount extends Model
{
    protected $table = 'registrant_accounts';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'password',
        'created_at',
    ];

    /**
     * AuthController's shared account-lookup code logs `$account->id` for
     * any account type (User, RegistrantAccount) interchangeably -- this
     * gives a RegistrantAccount (whose real key is `email`, not `id`) the
     * same interface so that code doesn't need a type check.
     */
    public function getIdAttribute(): string
    {
        return (string)$this->email;
    }
}
