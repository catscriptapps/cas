<?php
// /src/Controller/RegistrationsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Registration;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin-only management of submitted registrations: search/filter/list,
 * edit (including manually marking a registration Paid/Unpaid for
 * e-transfer/cash payments that never touch PayPal), and delete.
 *
 * There's no "create" here on purpose -- registrations only ever originate
 * from the public wizard (RegisterController), same as the legacy
 * cas-sports admin screen this is modeled on.
 */
class RegistrationsController
{
    use RecentActivityLogger;

    /**
     * Prepare data for the Registrations list page. Supports the same
     * per-column text filters + sortable columns + infinite scroll pattern
     * as UsersController::index() (see resources/js/components/data-table.js):
     * `filter[user]` (name, email, or phone), `filter[division]` (division
     * name or position), `filter[requests]` (heard-about-us source or
     * special-requests note), `filter[status]` ("paid"/"unpaid"/"current"/
     * "archived"), each a case-insensitive LIKE, ANDed together; `sort`
     * (user/division/requests/joined/payment/status) + `dir`; `page`.
     */
    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = Registration::with(['division', 'province', 'source'])
            ->leftJoin('divisions', 'registrations.division_id', '=', 'divisions.division_id')
            ->leftJoin('sources', 'registrations.hear_about_us', '=', 'sources.entry_id')
            ->select('registrations.*');

        if (!empty($filters['user'])) {
            $needle = $filters['user'];
            $builder->where(function ($q) use ($needle) {
                $q->where('registrations.full_name', 'LIKE', "%{$needle}%")
                    ->orWhere('registrations.email', 'LIKE', "%{$needle}%")
                    ->orWhere('registrations.phone', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['division'])) {
            $needle = $filters['division'];
            $builder->where(function ($q) use ($needle) {
                $q->where('divisions.division', 'LIKE', "%{$needle}%")
                    ->orWhere('registrations.position', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['requests'])) {
            $needle = $filters['requests'];
            $builder->where(function ($q) use ($needle) {
                $q->where('sources.hear_about_us', 'LIKE', "%{$needle}%")
                    ->orWhere('registrations.special_requests', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['status'])) {
            $needle = strtolower($filters['status']);
            if (str_contains('paid', $needle)) {
                $builder->where('registrations.has_paid', 1);
            } elseif (str_contains('unpaid', $needle)) {
                $builder->where('registrations.has_paid', 0);
            } elseif (str_contains('archived', $needle)) {
                $builder->where('registrations.status_id', '!=', 1);
            } elseif (str_contains('current', $needle)) {
                $builder->where('registrations.status_id', 1);
            }
        }

        $totalFiltered = (clone $builder)->count();

        $sortColumns = [
            'user'     => 'registrations.full_name',
            'division' => 'divisions.division',
            'requests' => 'sources.hear_about_us',
            'joined'   => 'registrations.date_created',
            'status'   => 'registrations.status_id',
        ];

        if ($sort === 'payment') {
            // Groups Unpaid/Paid together first, then orders by dollar amount
            // within each group -- more useful than has_paid alone since the
            // Payment column shows both the badge and the amount.
            $builder->orderBy('registrations.has_paid', $dir)
                ->orderBy('registrations.amount_paid', $dir);
        } elseif (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('registrations.date_created', 'desc');
        }

        $registrations = $builder->offset($offset)->limit($perPage)->get();

        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($r) => ['rowHtml' => self::renderRow($r)], $registrations->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $registrations->count(),
                    'hasMore' => ($offset + $registrations->count()) < $totalFiltered,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($registrations as $registration) {
            $html .= self::renderRow($registration);
        }

        $GLOBALS['registrationRows'] = $html;
        $GLOBALS['title'] = "Registrations";
        $GLOBALS['totalRegistrationsCount'] = $totalFiltered;
    }

    public static function renderRow(Registration $registration): string
    {
        $rowItem = $registration->toArray();
        $rowItem['division_name'] = $registration->division->division ?? 'N/A';
        $rowItem['province_name'] = $registration->province->region ?? '';
        $rowItem['source_label'] = $registration->source->hear_about_us ?? '';
        $rowItem['encoded_id'] = IdEncoder::encode((int)$registration->entry_id);
        $rowItem['created_at_formatted'] = $registration->date_created ? $registration->date_created->format('M j, Y') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/registrations/data-row.php';

        ob_start();
        try {
            $assetBase = getAssetBase();
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='6'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    /**
     * Admin edit -- fields + the Paid/Unpaid and Active/Archived toggles.
     * amount_paid is intentionally not settable here: it's driven by
     * whatever PayPal actually captured, or left at 0 until an admin flips
     * has_paid on for a manually-confirmed payment (see save() below).
     */
    public function save(array $data): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $encodedId = $data['encoded_id'] ?? null;
            if (empty($encodedId)) {
                throw new \Exception('Missing registration id.');
            }

            $registrationId = IdEncoder::decode($encodedId);
            $registration = $registrationId ? Registration::find($registrationId) : null;
            if (!$registration) {
                throw new \Exception('Registration not found.');
            }

            $registration->full_name = $data['full_name'] ?? $registration->full_name;
            $registration->email     = $data['email'] ?? $registration->email;
            $registration->phone     = $data['phone'] ?? $registration->phone;
            $registration->city      = $data['city'] ?? $registration->city;
            $registration->position  = $data['position'] ?? $registration->position;
            $registration->team_name = $data['team_name'] ?? $registration->team_name;

            $wasPaid = (int)$registration->has_paid === 1;
            $nowPaid = array_key_exists('has_paid', $data) ? (bool)$data['has_paid'] : $wasPaid;
            $registration->has_paid = $nowPaid ? 1 : 0;

            // Only move the dollar amount when an admin is manually turning
            // payment ON for the first time (e.g. e-transfer/cash) -- never
            // overwrite a real PayPal-captured amount, and never touch it on
            // an unrelated field edit.
            if ($nowPaid && !$wasPaid && empty($registration->transaction_id)) {
                $registration->amount_paid = $registration->division->price ?? $registration->amount_paid;
            } elseif (!$nowPaid) {
                $registration->amount_paid = 0;
                $registration->transaction_id = null;
            }

            if (array_key_exists('status_id', $data)) {
                $registration->status_id = (int)$data['status_id'] === 1 ? 1 : 0;
            }

            $registration->save();
            $registration->load(['division', 'province', 'source']);

            static::logActivity("Updated registration: {$registration->full_name}", 'Registrations', $registration->entry_id);

            return [
                'success' => true,
                'data' => $registration->toArray(),
                'rowHtml' => self::renderRow($registration),
                'messages' => ['Registration saved successfully.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete(?string $id): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $registration = $rawId ? Registration::find($rawId) : null;
            if (!$registration) {
                throw new \Exception('Failed to delete registration.');
            }

            $name = $registration->full_name;

            if ($registration->delete()) {
                static::logActivity("Deleted registration: {$name}", 'Registrations');
                return ['success' => true, 'messages' => ['Registration deleted successfully.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete registration.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
