<?php
// /server/api/change-password.php

declare(strict_types=1);

use Src\Controller\UsersController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    exit;
}

if (!AuthService::userId()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$controller = new UsersController();
$result = $controller->changePassword($input);

json_response($result, $result['success'] ? 200 : 400);
