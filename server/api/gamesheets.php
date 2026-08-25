<?php
// /server/api/gamesheets.php

declare(strict_types=1);

use Src\Controller\GamesheetsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new GamesheetsController();

    if ($method === 'GET') {
        $action = $_GET['action'] ?? '';

        if ($action === 'get_rosters') {
            json_response($controller->getRosters($_GET['game_id'] ?? ''));
            exit;
        }

        json_response($controller->index());
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $action = $input['action'] ?? '';

        if ($action === 'update_stat') {
            // Admin-only, unlike legacy which left this endpoint unauthenticated
            // (any client could POST a stat change) -- matches every other
            // editable field in this app (Stats' inline auto-save, etc.).
            if (!AuthService::isAdmin()) {
                json_response(['success' => false, 'messages' => ['Admin access required.']], 403);
            }

            json_response($controller->updateStat($input));
            exit;
        }
    }

    json_response(['success' => false, 'messages' => ['Method not supported']], 405);
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
