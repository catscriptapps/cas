<?php
// /server/api/messages.php

declare(strict_types=1);

use Src\Controller\MessagesController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = array_merge($_GET, $_POST, json_decode(file_get_contents('php://input'), true) ?: []);

try {
    // 🛡️ SECURITY: Only allow Admins to access message management.
    // Guests can only POST to 'create' (Contact Form).
    $isAdmin = AuthService::isAdmin();
    $action = $input['action'] ?? ($method === 'POST' ? 'create' : 'list');

    if (!$isAdmin && $action !== 'create') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    if ($method === 'GET') {
        // Handle "View Message Details" (for the Admin Drawer)
        if (!empty($input['id'])) {
            $data = MessagesController::getForView((int)$input['id']);
            if (!$data) json_response(['success' => false, 'message' => 'Message not found'], 404);

            json_response(['success' => true, ...$data]);
        }

        // Handle "List/Folder" (Inbox, Sent, Archive)
        $folder = $input['folder'] ?? 'inbox';
        $paginated = MessagesController::paginate($folder, (int)($input['per_page'] ?? 50));

        json_response([
            'success' => true,
            'messages' => $paginated->items(),
            'total' => $paginated->total(),
            'unread_count' => MessagesController::getUnreadCount()
        ]);
    }

    if ($method === 'POST') {
        $id = isset($input['id']) ? (int)$input['id'] : null;

        switch ($action) {
            case 'create':
                // This remains open for guest Contact Forms
                $message = MessagesController::create($input);

                json_response([
                    'success' => true,
                    'message' => 'Message sent successfully!',
                    'data'    => $message,
                    // rowHtml is returned so Admin UI can live-update if they are currently looking at the inbox
                    'rowHtml' => $isAdmin ? MessagesController::renderRow($message) : null
                ]);
                break;

            case 'delete':
                if (!$id || !MessagesController::delete($id)) {
                    json_response(['success' => false, 'message' => 'Delete failed'], 400);
                }
                json_response(['success' => true, 'message' => 'Message deleted']);
                break;

            case 'archive':
                if ($id && MessagesController::archive($id)) {
                    json_response(['success' => true, 'message' => 'Message archived']);
                }
                json_response(['success' => false, 'message' => 'Archive failed'], 400);
                break;

            default:
                json_response(['success' => false, 'message' => "Action '{$action}' not recognized"], 400);
                break;
        }
    }
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
}
