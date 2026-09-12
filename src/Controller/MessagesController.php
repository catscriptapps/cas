<?php
// /src/Controller/MessagesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\ContactMessage;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin inbox for the public "Contact Us" form's submissions (see
 * server/api/contact.php, which creates the ContactMessage row this manages).
 * Flat CRUD-ish resource with no relational linkage to anything else,
 * modeled on IncidentReportsController's filter[]/sort/page contract.
 * Every mutating action here is gated to AuthService::isAdmin() specifically
 * (not just isLoggedIn()) -- this whole feature is admin-only, per how it's
 * surfaced (a dedicated icon in the topbar shown only to admins).
 */
class MessagesController
{
    use RecentActivityLogger;

    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        // Defaults to the active inbox (not archived) unless a view is
        // explicitly requested -- the Messages page's "Archived" tab passes
        // view=archived to flip this.
        $view = $_GET['view'] ?? 'inbox';
        $builder = ContactMessage::query();
        $builder->where('status_id', $view === 'archived' ? ContactMessage::STATUS_ARCHIVED : ContactMessage::STATUS_INBOX);

        if (!empty($filters['message'])) {
            $needle = $filters['message'];
            $builder->where(function ($q) use ($needle) {
                $q->where('full_name', 'LIKE', "%{$needle}%")
                    ->orWhere('email', 'LIKE', "%{$needle}%")
                    ->orWhere('subject', 'LIKE', "%{$needle}%")
                    ->orWhere('message', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['status'])) {
            $needle = strtolower($filters['status']);
            if (str_contains('unread', $needle)) {
                $builder->where('is_read', false);
            } elseif (str_contains('read', $needle)) {
                $builder->where('is_read', true);
            }
        }

        $totalFiltered = (clone $builder)->count();

        $sortColumns = [
            'sender' => 'full_name',
            'subject' => 'subject',
            'date' => 'created_at',
        ];
        if (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('created_at', 'desc');
        }

        $rows = $builder->offset($offset)->limit($perPage)->get();

        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort']) || isset($_GET['view'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($m) => ['rowHtml' => self::renderRow($m)], $rows->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $rows->count(),
                    'hasMore' => ($offset + $rows->count()) < $totalFiltered,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($rows as $row) {
            $html .= self::renderRow($row);
        }

        $GLOBALS['messageRows'] = $html;
        $GLOBALS['title'] = 'Messages';
        $GLOBALS['totalMessagesCount'] = $totalFiltered;
    }

    public static function renderRow(ContactMessage $message): string
    {
        $rowItem = $message->toArray();
        $rowItem['encoded_id'] = IdEncoder::encode((int)$message->entry_id);
        $rowItem['date_formatted'] = $message->created_at ? $message->created_at->format('M j, Y g:i A') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/messages/data-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='5'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    private static function guard(): void
    {
        if (!AuthService::isAdmin()) {
            throw new \Exception("You don't have permission to do that.");
        }
    }

    public function setRead($id, bool $isRead): array
    {
        try {
            self::guard();

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $message = $rawId ? ContactMessage::find($rawId) : null;
            if (!$message) {
                throw new \Exception('Message not found.');
            }

            $message->is_read = $isRead;
            $message->save();

            return [
                'success' => true,
                'rowHtml' => self::renderRow($message),
                'unreadCount' => ContactMessage::unreadInboxCount(),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function toggleArchive($id): array
    {
        try {
            self::guard();

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $message = $rawId ? ContactMessage::find($rawId) : null;
            if (!$message) {
                throw new \Exception('Message not found.');
            }

            $wasArchived = (int)$message->status_id === ContactMessage::STATUS_ARCHIVED;
            $message->status_id = $wasArchived ? ContactMessage::STATUS_INBOX : ContactMessage::STATUS_ARCHIVED;
            $message->save();

            static::logActivity(
                ($wasArchived ? 'Restored' : 'Archived') . " message from {$message->full_name}: {$message->subject}",
                'Messages',
                $message->entry_id
            );

            return [
                'success' => true,
                'archived' => !$wasArchived,
                'rowHtml' => self::renderRow($message),
                'unreadCount' => ContactMessage::unreadInboxCount(),
                'messages' => [$wasArchived ? 'Message restored to inbox.' : 'Message archived.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete($id): array
    {
        try {
            self::guard();

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $message = $rawId ? ContactMessage::find($rawId) : null;
            if (!$message) {
                throw new \Exception('Failed to delete. Message not found.');
            }

            $sender = $message->full_name;
            $subject = $message->subject;

            if ($message->delete()) {
                static::logActivity("Deleted message from {$sender}: {$subject}", 'Messages');
                return [
                    'success' => true,
                    'unreadCount' => ContactMessage::unreadInboxCount(),
                    'messages' => ['Message deleted.'],
                ];
            }

            return ['success' => false, 'messages' => ['Failed to delete message.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
