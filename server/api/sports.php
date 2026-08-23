<?php
// /server/api/sports.php

declare(strict_types=1);

use Src\Controller\SportsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new SportsController();

    if ($method === 'GET') {
        // The public registration wizard's plain GET (no ?q=) doesn't
        // require auth -- only the admin search/list does.
        if (isset($_GET['q']) && !AuthService::userId()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }

        if (isset($_GET['q'])) {
            $controller->index();
            exit;
        }

        $wantsAll = isset($_GET['status']) && $_GET['status'] === 'all';
        if ($wantsAll && !AuthService::userId()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }

        json_response(['success' => true, 'data' => $wantsAll ? SportsController::listAll() : SportsController::list()]);
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
