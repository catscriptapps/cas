<?php
// /src/Service/AuthService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\User;
use App\Models\UserType;

/**
 * Class AuthService
 * Centralized authentication service updated for the modernized users table.
 */
class AuthService
{
    /**
     * Retrieves the currently logged-in user from the database.
     * Uses the standard 'id' primary key.
     */
    public static function currentUser(): ?User
    {
        self::ensureSession();

        return isset($_SESSION['user_id'])
            ? User::find((int)$_SESSION['user_id'])
            : null;
    }

    /**
     * Check if the user has access to a specific app. There's currently
     * only one backend role (Admin), so any signed-in backend account can
     * reach any of the (few) modules -- this is a hook for role-gating apps
     * again once CAS grows a second role.
     */
    public static function hasAccess(string $appName): bool
    {
        return self::isLoggedIn();
    }

    /**
     * Ensures that a PHP session is started with a 2-week persistence.
     */
    protected static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Determine if the current user is an admin.
     * Admin is defined as having user_type_id === UserType::ADMIN.
     */
    public static function isAdmin(): bool
    {
        self::ensureSession();
        $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($uid === 0) {
            return false;
        }

        $user = User::find($uid);

        return $user !== null && $user->isType(UserType::ADMIN);
    }

    /**
     * Determine if the current user is Cat (ID 1).
     */
    public static function isCat(): bool
    {
        self::ensureSession();
        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1;
    }

    /**
     * Checks if a user is currently logged in.
     */
    public static function isLoggedIn(): bool
    {
        self::ensureSession();
        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
    }

    /**
     * Attempt to authenticate a user.
     * Only allows users with status_id = 1 (Active/Verified).
     */
    public static function login(string $email, string $password, ?string $returnTo = null): array
    {
        self::ensureSession();

        // --- Priority 1: Attempt login as a backend User ---
        $user = User::where('email', $email)->first();
        if ($user && password_verify($password, $user->password)) {
            // CHECK STATUS: Only active users can proceed
            if ((int)$user->status_id !== 1) {
                return [
                    'success' => false,
                    'unverified' => true,
                    'messages' => ['Account not activated. Please verify your email.']
                ];
            }

            // Set User Session Data
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_full_name'] = $user->full_name;
            $_SESSION['account_type'] = 'user'; // Distinguish account type

            // Generate secure API token
            $token = bin2hex(random_bytes(32));
            $user->api_token = $token;
            $user->user_last_log = date('Y-m-d H:i:s');
            $user->save();

            return [
                'success' => true,
                'api_token' => $token,
                'messages' => ['Login successful!'],
                'redirect_url' => '/dashboard' // Explicit redirect for users
            ];
        }

        return ['success' => false, 'messages' => ['Invalid email or password.']];
    }

    /**
     * Authenticate an API request by checking an incoming Bearer Token.
     *
     * @param string $token
     * @return User|null
     */
    public static function getUserByToken(string $token): ?User
    {
        return User::where('api_token', $token)->first();
    }

    /**
     * Logs out the current user.
     */
    public static function logout(): void
    {
        self::ensureSession();

        // If a user session exists, nullify their API token in the database
        if (isset($_SESSION['user_id'])) {
            $user = User::find((int)$_SESSION['user_id']);
            if ($user) {
                $user->api_token = null;
                $user->save();
            }
        }

        session_unset();
        session_destroy();
    }

    /**
     * Get the current user ID.
     */
    public static function userId(): ?int
    {
        self::ensureSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Identifies the currently signed-in account, if any.
     *
     * @return array{type: string, id: int}|null
     */
    public static function currentAccountContext(): ?array
    {
        self::ensureSession();

        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
            return ['type' => 'user', 'id' => (int)$_SESSION['user_id']];
        }

        return null;
    }
}
