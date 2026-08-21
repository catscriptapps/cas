<?php
// /server/api/notifications.php

declare(strict_types=1);

use Src\Controller\NotificationsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $account = AuthService::currentAccountContext();
    if (!$account) {
        json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    }

    if ($method === 'GET') {
        $unreadCount = NotificationsController::getUnreadCount($account['type'], $account['id']);
        $latest = $unreadCount > 0 ? NotificationsController::getLatestUnread($account['type'], $account['id']) : null;

        json_response([
            'success'      => true,
            'unread_count' => $unreadCount,
            'latest'       => $latest ? [
                'id'      => $latest->id,
                'subject' => $latest->subject,
                'message' => $latest->message,
                'link'    => $latest->link,
            ] : null,
        ]);
    }

    if ($method !== 'POST') {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }

    $action = strtoupper($input['_method'] ?? '');

    if ($action === 'MARK_ALL_READ') {
        json_response(NotificationsController::markAllRead($account['type'], $account['id']));
    }

    if ($action === 'MARK_READ') {
        json_response(NotificationsController::markRead((int)($input['id'] ?? 0), $account['type'], $account['id']));
    }

    json_response(['success' => false, 'messages' => ['Unknown action.']], 400);
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
