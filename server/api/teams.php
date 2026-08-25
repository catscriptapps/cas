<?php
// /server/api/teams.php

declare(strict_types=1);

use Src\Controller\TeamsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::userId()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new TeamsController();

    if ($method === 'GET') {
        json_response($controller->index($_GET));
    }

    if ($method === 'POST') {
        json_response($controller->save($input));
    }

    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
