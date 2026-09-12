<?php
// /server/api/messages.php

declare(strict_types=1);

use Src\Controller\MessagesController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// Admin-only, not just "logged in" -- see MessagesController's docblock.
if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new MessagesController();

    if ($method === 'GET') {
        $controller->index();
        exit;
    }

    if ($method === 'POST') {
        $override = strtoupper($input['_method'] ?? '');
        if ($override === 'DELETE') {
            json_response($controller->delete($input['id'] ?? 0));
        }

        $action = $input['action'] ?? '';
        $id = $input['encoded_id'] ?? ($input['id'] ?? 0);

        $result = match ($action) {
            'mark-read' => $controller->setRead($id, true),
            'mark-unread' => $controller->setRead($id, false),
            'toggle-archive' => $controller->toggleArchive($id),
            default => ['success' => false, 'messages' => ['Unknown action.']],
        };

        json_response($result);
    }

    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
