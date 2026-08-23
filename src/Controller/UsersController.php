<?php
// /src/Controller/UsersController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\User;
use App\Models\UserType;
use App\Utils\IdEncoder;
use Src\Service\AuthService;
use App\Traits\RecentActivityLogger;

class UsersController
{
    use RecentActivityLogger;

    /**
     * Handle Delete
     * @param string|null $id
     * @return array
     */
    public function delete(?string $id): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $user = User::find($rawId);
            if (!$user) throw new \Exception("Failed to delete user.");

            $actingUser = AuthService::currentUser();
            if ($actingUser && (int)$actingUser->id === (int)$user->id) {
                throw new \Exception("You cannot delete your own account while signed in.");
            }
            if (in_array((int)$user->id, [1], true)) {
                throw new \Exception("This core account cannot be deleted.");
            }

            $userName = $user->full_name;
            $userEmail = $user->email;

            if ($user->delete()) {
                static::logActivity("Deleted user account: {$userName} ({$userEmail})", 'Users');
                return ['success' => true, 'messages' => ['User deleted successfully.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete user.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Prepare data for the Users List Page. Supports the per-column text
     * filters + sortable columns + infinite scroll driven by
     * resources/js/components/data-table.js: `filter[user]` (name or
     * email), `filter[city]`, `filter[role]`, `filter[status]`, each a
     * case-insensitive LIKE, ANDed together; `sort` + `dir` for column
     * sorting (defaults to newest-first); `page` for pagination. A plain
     * unparameterized request renders the full page with the first batch
     * already server-rendered; any of page/filter/sort being present
     * signals an AJAX call, answered with JSON instead.
     * @return void
     */
    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = User::with(['country', 'region'])
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftJoin('regions', 'users.region_id', '=', 'regions.id')
            ->leftJoin('users_types', 'users.user_type_id', '=', 'users_types.user_type_id')
            ->select('users.*');

        if (!empty($filters['user'])) {
            $needle = $filters['user'];
            $builder->where(function ($q) use ($needle) {
                $q->where('users.first_name', 'LIKE', "%{$needle}%")
                    ->orWhere('users.last_name', 'LIKE', "%{$needle}%")
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$needle}%"])
                    ->orWhere('users.email', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['city'])) {
            $builder->where(function ($q) use ($filters) {
                $q->where('users.city', 'LIKE', '%' . $filters['city'] . '%')
                    ->orWhere('regions.region', 'LIKE', '%' . $filters['city'] . '%')
                    ->orWhere('countries.country', 'LIKE', '%' . $filters['city'] . '%');
            });
        }
        if (!empty($filters['role'])) {
            $builder->where('users_types.user_type', 'LIKE', '%' . $filters['role'] . '%');
        }
        if (!empty($filters['status'])) {
            $needle = strtolower($filters['status']);
            if (str_contains('current', $needle)) {
                $builder->where('users.status_id', 1);
            } elseif (str_contains('archived', $needle)) {
                $builder->where('users.status_id', '!=', 1);
            }
        }

        $totalFiltered = (clone $builder)->count();

        $sortColumns = [
            'city' => 'users.city',
            'role' => 'users_types.user_type',
            'joined' => 'users.date_created',
            'status' => 'users.status_id',
        ];

        if ($sort === 'user') {
            $builder->orderBy('users.first_name', $dir)->orderBy('users.last_name', $dir);
        } elseif (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('users.date_created', 'desc');
        }

        $users = $builder->offset($offset)
            ->limit($perPage)
            ->get();

        // AJAX response
        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort'])) {
            header('Content-Type: application/json');

            // Standard Table Response
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($u) => ['rowHtml' => self::renderRow($u)], $users->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $users->count(),
                    'hasMore' => ($offset + $users->count()) < $totalFiltered
                ]
            ]);
            exit;
        }

        // Standard Page Load logic remains the same...
        $html = '';
        foreach ($users as $user) {
            $html .= self::renderRow($user);
        }

        $GLOBALS['userRows'] = $html;
        $GLOBALS['title'] = "Users";
        $GLOBALS['totalUsersCount'] = $totalFiltered;
    }

    /**
     * Render individual table row HTML
     * @param User $user
     * @return string
     */
    public static function renderRow(\App\Models\User $user): string
    {
        $rowItem = $user->toArray();

        // Fix for PHP Warning: Use helper to ensure full_name exists
        if (!isset($rowItem['full_name'])) {
            $rowItem['full_name'] = $user->full_name;
        }

        $GLOBALS['assetBase'] = getAssetBase();

        // Location Mapping
        $rowItem['country_name'] = $user->country->country ?? 'N/A';
        $rowItem['region_name']  = $user->region->region ?? 'N/A';

        // Encoding ID for security
        $rowItem['encoded_id'] = IdEncoder::encode((int)$user->id);
        $rowItem['created_at_formatted'] = $user->date_created ? $user->date_created->format('M j, Y') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/users/data-row.php';

        ob_start();
        try {
            // Passing variables explicitly to prevent Scope issues
            $assetBase = getAssetBase();
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='6'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    /**
     * Handle Create or Update for Users
     * @param array $data
     * @return array
     */
    public function save(array $data): array
    {
        try {
            $encodedId = $data['encoded_id'] ?? null;
            $email = trim($data['email'] ?? '');
            $isNew = empty($encodedId);

            if (empty($email)) throw new \Exception("Email address is required.");

            $userId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $user = $userId ? User::find($userId) : new User();

            if (!$user) throw new \Exception("User not found.");

            // --- PERMISSION GATE ---
            // Only one backend role exists (Admin), so any signed-in backend
            // account may manage the user directory.
            $actingUser = AuthService::currentUser();
            if (!$actingUser) {
                throw new \Exception("You don't have permission to do that.");
            }

            // Email uniqueness check
            $existingQuery = User::where('email', $email);
            if ($user->exists) {
                $existingQuery->where('id', '!=', $user->id);
            }
            if ($existingQuery->exists()) {
                throw new \Exception("The email address '{$email}' is already in use.");
            }

            $user->first_name = $data['first_name'] ?? '';
            $user->last_name  = $data['last_name'] ?? '';
            $user->email      = $email;
            $user->city       = $data['city'] ?? null;
            $user->country_id = (int)($data['country_id'] ?? 1);
            $tableRegionId    = (int)($data['region_id'] ?? 0);
            $user->region_id  = $tableRegionId > 0 ? $tableRegionId : null;

            if (!empty($data['password'])) {
                $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            if ($isNew) {
                $user->user_type_id = UserType::ADMIN;
            }

            // Core Account Guard: the founding account must stay an Admin.
            if (!$isNew && in_array((int)$user->id, [1], true) && $user->user_type_id !== UserType::ADMIN) {
                throw new \Exception("This core account's role cannot be changed.");
            }

            if ($isNew) {
                // Admin-created accounts are active immediately -- there's no
                // public self-registration flow to gate behind email
                // verification.
                $user->status_id = 1;
            } elseif (array_key_exists('status_id', $data)) {
                $newStatusId = (int)$data['status_id'] === 1 ? 1 : 0;

                if ($newStatusId === 0) {
                    // Archiving sets status_id != 1, which blocks sign-in
                    // (AuthController::login()) -- guard against an admin
                    // locking themselves, or one of the two core accounts,
                    // out entirely.
                    if ($actingUser && (int)$actingUser->id === (int)$user->id) {
                        throw new \Exception("You cannot archive your own account while signed in.");
                    }
                    if (in_array((int)$user->id, [1], true)) {
                        throw new \Exception("This core account cannot be archived.");
                    }
                }

                $user->status_id = $newStatusId;
            }

            $user->save();

            $user->load(['country', 'region']);

            $actionLabel = $isNew ? "Created user profile" : "Updated user profile";
            static::logActivity("{$actionLabel}: {$user->full_name} ({$user->email})", 'Users');

            return [
                'success'  => true,
                'user_id'  => $user->id,
                'data'     => $user->toArray(),
                'rowHtml'  => self::renderRow($user),
                'messages' => ['User saved successfully.']
            ];
        } catch (\Throwable $e) {
            static::logActivity("User save error: " . $e->getMessage(), 'Users');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Self-service password change for the currently signed-in user.
     * Requires the correct current password before setting a new one.
     * @param array $data
     * @return array
     */
    public function changePassword(array $data): array
    {
        try {
            $user = AuthService::currentUser();
            if (!$user) {
                throw new \Exception("Authentication required.");
            }

            $currentPassword = (string)($data['current_password'] ?? '');
            $newPassword     = (string)($data['new_password'] ?? '');
            $confirmPassword = (string)($data['confirm_password'] ?? '');

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                throw new \Exception("All password fields are required.");
            }

            if (!password_verify($currentPassword, $user->password)) {
                throw new \Exception("Current password is incorrect.");
            }

            if ($newPassword !== $confirmPassword) {
                throw new \Exception("New passwords do not match.");
            }

            if (strlen($newPassword) < 8) {
                throw new \Exception("New password must be at least 8 characters long.");
            }

            if (password_verify($newPassword, $user->password)) {
                throw new \Exception("New password must be different from your current password.");
            }

            $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
            $user->save();

            static::logActivity("Changed account password", 'Users');

            return ['success' => true, 'messages' => ['Password updated successfully.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

}
