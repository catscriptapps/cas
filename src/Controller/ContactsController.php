<?php
// /src/Controller/ContactsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Contact;
use App\Models\ContactRole;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin management of the Contact Directory (league officials, timekeepers,
 * emergency contacts, township/city contacts). Modeled on cas-sports'
 * ContactsController, but the list endpoint follows this project's
 * filter[]/sort/page data-table contract (see resources/js/components/data-
 * table.js) rather than legacy's single `?q=` search box.
 */
class ContactsController
{
    use RecentActivityLogger;

    /**
     * All contact roles, for the role dropdown -- a real endpoint instead of
     * legacy's pattern of scraping a hidden <select> already in the DOM.
     */
    public static function roles()
    {
        return ContactRole::orderBy('role_name')->get();
    }

    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = Contact::with('role');

        if (!empty($filters['contact'])) {
            $needle = $filters['contact'];
            $builder->where(function ($q) use ($needle) {
                $q->where('full_name', 'LIKE', "%{$needle}%")
                    ->orWhere('organization', 'LIKE', "%{$needle}%")
                    ->orWhere('email', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['leagues'])) {
            $builder->where('leagues', 'LIKE', '%' . $filters['leagues'] . '%');
        }
        if (!empty($filters['role'])) {
            $needle = $filters['role'];
            $builder->whereHas('role', fn($rq) => $rq->where('role_name', 'LIKE', "%{$needle}%"));
        }
        if (!empty($filters['emergency'])) {
            $needle = strtolower($filters['emergency']);
            if (str_contains('priority', $needle) || str_contains('emergency', $needle) || str_contains('yes', $needle)) {
                $builder->where('is_emergency', 1);
            } elseif (str_contains('standard', $needle) || str_contains('no', $needle)) {
                $builder->where('is_emergency', 0);
            }
        }

        $totalFiltered = (clone $builder)->count();

        $sortColumns = [
            'contact' => 'full_name',
            'leagues' => 'leagues',
            'phone' => 'phone',
            'emergency' => 'is_emergency',
        ];

        if ($sort === 'role') {
            // The only sort key that needs a join -- role name lives on the
            // related contacts_roles table, not a column on contacts itself.
            $builder->leftJoin('contacts_roles', 'contacts.role_id', '=', 'contacts_roles.id')
                ->orderBy('contacts_roles.role_name', $dir)
                ->select('contacts.*');
        } elseif (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('full_name', 'asc');
        }

        $contacts = $builder->offset($offset)->limit($perPage)->get();

        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($c) => ['rowHtml' => self::renderRow($c)], $contacts->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $contacts->count(),
                    'hasMore' => ($offset + $contacts->count()) < $totalFiltered,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($contacts as $contact) {
            $html .= self::renderRow($contact);
        }

        $GLOBALS['contactRows'] = $html;
        $GLOBALS['title'] = 'Contact Directory';
        $GLOBALS['totalContactsCount'] = $totalFiltered;
    }

    public static function renderRow(Contact $contact): string
    {
        $rowItem = $contact->toArray();
        $rowItem['encoded_id'] = IdEncoder::encode((int)$contact->entry_id);
        $rowItem['role_label'] = $contact->role->role_name ?? 'General Contact';

        $path = __DIR__ . '/../../resources/views/components/contacts/data-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='6'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    public function save(array $data): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $fullName = trim((string)($data['full_name'] ?? ''));
            if ($fullName === '') {
                throw new \Exception('Full name is required.');
            }

            $roleId = (int)($data['role_id'] ?? 0);
            if (!$roleId || !ContactRole::find($roleId)) {
                throw new \Exception('Please choose a valid role.');
            }

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);
            $entryId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $contact = $entryId ? Contact::find($entryId) : new Contact();

            if (!$contact) {
                throw new \Exception('Contact not found.');
            }

            $contact->full_name = $fullName;
            $contact->organization = $data['organization'] ?? null;
            $contact->email = $data['email'] ?? null;
            $contact->phone = $data['phone'] ?? null;
            $contact->leagues = $data['leagues'] ?? 'All';
            $contact->is_emergency = !empty($data['is_emergency']) ? 1 : 0;
            $contact->role_id = $roleId;
            if (array_key_exists('status_id', $data)) {
                $contact->status_id = (int)$data['status_id'] === 1 ? 1 : 0;
            } elseif ($isNew) {
                $contact->status_id = Contact::STATUS_ACTIVE;
            }

            $contact->save();
            $contact->load('role');

            $roleName = $contact->role->role_name ?? 'Contact';
            $actionLabel = $isNew ? 'Added new' : 'Updated';
            static::logActivity("{$actionLabel} {$roleName}: {$contact->full_name}", 'Contacts', $contact->entry_id);

            return [
                'success' => true,
                'rowHtml' => self::renderRow($contact),
                'messages' => ['Contact saved successfully.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete($id): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $contact = $rawId ? Contact::with('role')->find($rawId) : null;
            if (!$contact) {
                throw new \Exception('Failed to delete contact.');
            }

            $name = $contact->full_name;
            $role = $contact->role->role_name ?? 'Contact';

            if ($contact->delete()) {
                static::logActivity("Deleted {$role}: {$name}", 'Contacts');
                return ['success' => true, 'messages' => ['Contact removed.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete contact.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
