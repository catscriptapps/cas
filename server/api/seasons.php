<?php
// /server/api/seasons.php

declare(strict_types=1);

use Src\Controller\SeasonsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new SeasonsController();

    if ($method === 'GET') {
        // Public GET -- Schedules is a guest-visible feature (see
        // NavigationConfig::getProtectedPaths()), so the season list's
        // filter/sort/page data-table calls must work for guests too.
        $controller->index();
        exit;
    }

    if ($method === 'POST') {
        if (!AuthService::userId()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }

        $override = strtoupper($input['_method'] ?? '');
        $result = $override === 'DELETE' ? $controller->delete($input['id'] ?? 0) : $controller->save($input);

        json_response($result);
    }

    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
