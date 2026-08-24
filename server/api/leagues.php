<?php
// /server/api/leagues.php

declare(strict_types=1);

use Src\Controller\LeaguesController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new LeaguesController();

    if ($method === 'GET') {
        $isAdminQuery = isset($_GET['filter']) || isset($_GET['sort']) || isset($_GET['page']);
        if ($isAdminQuery && !AuthService::userId()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }

        if ($isAdminQuery) {
            $controller->index();
            exit;
        }

        $sportId = isset($_GET['sport_id']) ? (int)$_GET['sport_id'] : null;

        $wantsAll = isset($_GET['status']) && $_GET['status'] === 'all';
        if ($wantsAll && !AuthService::userId()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }

        json_response(['success' => true, 'data' => $wantsAll ? LeaguesController::listAll($sportId) : LeaguesController::list($sportId)]);
    }

    if ($method === 'POST') {
        if (!AuthService::userId()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }

        $override = strtoupper($input['_method'] ?? '');
        $isDelete = ($override === 'DELETE');
        $result = $isDelete ? $controller->delete($input['id'] ?? 0) : $controller->save($input);

        json_response($result);
    }

    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
