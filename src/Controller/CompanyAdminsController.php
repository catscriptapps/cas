<?php
// /src/Controller/CompanyAdminsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Company;
use App\Models\User;
use App\Models\UserType;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;

/**
 * Manages the Company Admin users assigned to a single company, from the
 * Companies page's detail view. Deliberately separate from UsersController
 * -- that controller's create path is reachable without a session (guest
 * self-registration), and this feature must stay strictly admin-only, so
 * it gets its own fully admin-gated endpoint instead of reusing that path.
 */
class CompanyAdminsController
{
    use RecentActivityLogger;

    /**
     * List the Company Admins assigned to a company.
     * @param string $encodedCompanyId
     * @return array
     */
    public function list(string $encodedCompanyId): array
    {
        try {
            $companyId = IdEncoder::decode($encodedCompanyId);
            $company = $companyId ? Company::find($companyId) : null;
            if (!$company) throw new \Exception("Company not found.");

            $admins = $company->admins()
                ->orderBy('date_created', 'desc')
                ->get()
                ->filter(fn(User $u) => $u->isType(UserType::COMPANY_ADMIN))
                ->map(fn(User $u) => [
                    'encoded_id' => IdEncoder::encode((int)$u->id),
                    'full_name'  => $u->full_name,
                    'email'      => $u->email,
                    'is_active'  => (int)$u->status_id === 1,
                    'joined'     => $u->date_created ? $u->date_created->format('M j, Y') : 'N/A',
                ])
                ->values();

            return ['success' => true, 'data' => $admins];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Create a new Company Admin user tied to a company.
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $companyId = IdEncoder::decode((string)($data['company_id'] ?? ''));
            $company = $companyId ? Company::find($companyId) : null;
            if (!$company) throw new \Exception("A valid company is required.");

            $firstName = trim($data['first_name'] ?? '');
            $lastName  = trim($data['last_name'] ?? '');
            $email     = trim($data['email'] ?? '');
            $password  = (string)($data['password'] ?? '');
            $confirm   = (string)($data['password_confirmation'] ?? '');

            if ($firstName === '' || $lastName === '' || $email === '') {
                throw new \Exception("First name, last name, and email are required.");
            }
            if ($password === '' || $password !== $confirm) {
                throw new \Exception("Passwords must match and cannot be empty.");
            }
            if (strlen($password) < 8) {
                throw new \Exception("Password must be at least 8 characters long.");
            }
            if (User::where('email', $email)->exists()) {
                throw new \Exception("The email address '{$email}' is already in use.");
            }

            $user = new User();
            $user->first_name     = $firstName;
            $user->last_name      = $lastName;
            $user->email          = $email;
            $user->password       = password_hash($password, PASSWORD_BCRYPT);
            $user->company_id     = $company->id;
            $user->user_type_id   = UserType::COMPANY_ADMIN;
            $user->status_id      = 1;
            $user->email_verified = true;
            $user->save();

            static::logActivity("Added company admin: {$user->full_name} ({$user->email}) to {$company->company_name}", 'Companies');

            return [
                'success'  => true,
                'messages' => ['Company admin added successfully.'],
                'data'     => [
                    'encoded_id' => IdEncoder::encode((int)$user->id),
                    'full_name'  => $user->full_name,
                    'email'      => $user->email,
                    'is_active'  => true,
                    'joined'     => $user->date_created ? $user->date_created->format('M j, Y') : 'Just now',
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Remove a Company Admin (permanently deletes the user account).
     * @param string $encodedUserId
     * @return array
     */
    public function delete(string $encodedUserId): array
    {
        try {
            $userId = IdEncoder::decode($encodedUserId);
            $user = $userId ? User::find($userId) : null;

            if (!$user || empty($user->company_id) || !$user->isType(UserType::COMPANY_ADMIN)) {
                throw new \Exception("Company admin not found.");
            }

            $name = $user->full_name;
            $user->delete();

            static::logActivity("Removed company admin: {$name}", 'Companies');

            return ['success' => true, 'messages' => ['Company admin removed successfully.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
