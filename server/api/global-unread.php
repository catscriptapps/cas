<?php
// /server/api/global-unread.php

declare(strict_types=1);

use Src\Controller\MessagesController;
use Src\Controller\NotificationsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Get the "Admin/System" messages count
    $messagesCount = MessagesController::getUnreadCount();

    // 2. Get the "General Notifications" (Bell icon)
    $account = AuthService::currentAccountContext();
    $notificationsCount = $account
        ? NotificationsController::getUnreadCount($account['type'], $account['id'])
        : 0;

    // 3. Get the "Messenger/Chat" messages (Our new chat system)
    $chatsCount = 0;

    json_response([
        'success' => true,
        'counts' => [
            'messages'      => (int)$messagesCount,
            'notifications' => (int)$notificationsCount,
            'chats'         => (int)$chatsCount
        ],
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
} catch (Throwable $e) {
    // Log the error internally if you have a logger
    json_response([
        'success' => false,
        'message' => 'Heartbeat failed: ' . $e->getMessage(),
    ], 500);
}
