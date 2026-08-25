<?php
// /server/api/schedules.php

declare(strict_types=1);

use Src\Controller\SchedulesController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new SchedulesController();

    if ($method === 'GET') {
        // Public GET -- guests can view schedules (see
        // NavigationConfig::getProtectedPaths()); only mutating it needs
        // admin.
        $result = $controller->index($_GET);
        json_response($result);
    }

    if ($method === 'POST') {
        if (!AuthService::isAdmin()) {
            json_response(['success' => false, 'messages' => ["You don't have permission to do that."]], 403);
        }

        $override = strtoupper($input['_method'] ?? '');
        $result = $override === 'DELETE' ? $controller->delete($input['id'] ?? 0) : $controller->save($input);

        json_response($result);
    }

    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
