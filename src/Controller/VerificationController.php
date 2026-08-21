<?php
// /src/Controller/VerificationController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Inspector;
use App\Models\User;
use App\Models\UserVerification;

class VerificationController
{
    /**
     * Verify a guest-registration/account-activation email/token pair
     * (shared across backend Users and Inspectors) and, on success, log the
     * matching account straight in.
     */
    public function verify(string $email, string $token): array
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $email = trim($email);
            if ($email === '' || $token === '') {
                throw new \Exception("Invalid verification link.");
            }

            $verification = UserVerification::find($email);
            if (!$verification) {
                throw new \Exception("No pending verification found for this email.");
            }

            if ($verification->isExpired(60)) {
                $verification->delete();
                throw new \Exception("This verification link has expired. Please register again.");
            }

            if (!password_verify($token, $verification->token)) {
                throw new \Exception("Invalid or already-used verification link.");
            }

            $user = User::where('email', $email)->first();
            if ($user) {
                $user->status_id = 1;
                $user->save();
                $verification->delete();

                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_full_name'] = $user->full_name;
                $_SESSION['account_type'] = 'user';

                return ['success' => true, 'messages' => ['Account verified.'], 'account_type' => 'user'];
            }

            $inspector = Inspector::where('email', $email)->first();
            if ($inspector) {
                $inspector->status_id = 1;
                $inspector->save();
                $verification->delete();

                $_SESSION['inspector_id'] = $inspector->id;
                $_SESSION['inspector_email'] = $inspector->email;
                $_SESSION['inspector_full_name'] = $inspector->full_name;
                $_SESSION['account_type'] = 'inspector';

                return ['success' => true, 'messages' => ['Account verified.'], 'account_type' => 'inspector'];
            }

            throw new \Exception("We could not find an account matching this email.");
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
