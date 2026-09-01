<?php
// /src/Controller/RegisterController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Division;
use App\Models\RegistrantAccount;
use App\Models\Registration;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;

/**
 * Handles the public registration form (guest-facing, no login required) --
 * distinct from RegistrationsController, which is the admin-only management
 * screen for reviewing/editing submitted registrations.
 */
class RegisterController
{
    use RecentActivityLogger;

    /**
     * Create a pending (unpaid) registration row from the wizard's final
     * step. Returns the encoded id + the amount due so the client can move
     * straight into the PayPal step.
     */
    public function create(array $data): array
    {
        try {
            $firstName = trim((string)($data['first_name'] ?? ''));
            $lastName  = trim((string)($data['last_name'] ?? ''));
            $email     = trim((string)($data['email'] ?? ''));
            $divisionId = (int)($data['division_id'] ?? 0);

            if ($firstName === '' || $lastName === '' || $email === '') {
                throw new \Exception('Name and email are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Please provide a valid email address.');
            }

            $division = $divisionId ? Division::find($divisionId) : null;
            if (!$division || (int)$division->status_id !== Division::STATUS_ACTIVE) {
                throw new \Exception('Please choose a valid division.');
            }

            $provinceId = (int)($data['province_id'] ?? 0);
            $fullName = trim("{$firstName} {$lastName}");

            // Field set (full_name, age as a string, province_id, position,
            // has_paid as a plain int) matches the legacy cas-sports
            // registrations table exactly -- see Registration model docblock.
            $registration = Registration::create([
                'division_id'      => $division->division_id,
                'full_name'        => $fullName,
                'age'              => isset($data['age']) && $data['age'] !== '' ? (string)(int)$data['age'] : null,
                'email'            => $email,
                'phone'            => $data['phone'] ?? null,
                'address'          => $data['address'] ?? null,
                'city'             => $data['city'] ?? null,
                'province_id'      => $provinceId > 0 ? $provinceId : null,
                'postal_code'      => $data['postal_code'] ?? null,
                'position'         => $data['position'] ?? null,
                'hear_about_us'    => !empty($data['hear_about_us']) ? (int)$data['hear_about_us'] : null,
                'team_name'        => $data['team_name'] ?? null,
                'special_requests' => $data['special_requests'] ?? null,
                'has_paid'         => 0,
                'amount_paid'      => 0,
                'status_id'        => Registration::STATUS_ACTIVE,
            ]);

            static::logActivity(
                "New registration: {$registration->full_name} ({$division->division})",
                'Registrations',
                $registration->entry_id
            );

            $accountMessage = $this->maybeCreateAccount($email, $data);

            return [
                'success' => true,
                'encoded_id' => IdEncoder::encode($registration->entry_id),
                'amount_due' => (float)$division->price,
                'division_label' => $division->division,
                'messages' => array_filter([
                    'Registration received. Continue to payment to confirm your spot.',
                    $accountMessage,
                ]),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * The "create a password" step-4 fields are entirely optional -- a blank
     * password just means the registrant skipped self-service login setup
     * and stays reachable only through the admin Registrations screen, same
     * as before this feature existed. Silently no-ops on a bad/mismatched
     * password rather than failing the whole registration, since the
     * registration itself already succeeded by the time this runs.
     */
    private function maybeCreateAccount(string $email, array $data): ?string
    {
        $password = (string)($data['password'] ?? '');
        $confirmation = (string)($data['password_confirmation'] ?? '');

        if ($password === '') {
            return null;
        }
        if ($password !== $confirmation) {
            return 'Note: your password and confirmation did not match, so an account was not created. You can set one up later from the Sign In page.';
        }
        if (strlen($password) < 8) {
            return 'Note: your password was too short (8 characters minimum), so an account was not created. You can set one up later from the Sign In page.';
        }

        $account = RegistrantAccount::firstOrNew(['email' => $email]);
        $account->password = password_hash($password, PASSWORD_DEFAULT);
        if (!$account->exists) {
            $account->created_at = date('Y-m-d H:i:s');
        }
        $account->save();

        return 'Your account is ready -- sign in anytime to check your registration status.';
    }
}
