<?php
// /server/api/contacts.php

declare(strict_types=1);

use Src\Controller\ContactsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::userId()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new ContactsController();

    if ($method === 'GET') {
        $controller->index();
        exit;
    }

    if ($method === 'POST') {
        $override = strtoupper($input['_method'] ?? '');
        $isDelete = ($override === 'DELETE');
        $result = $isDelete ? $controller->delete($input['id'] ?? 0) : $controller->save($input);

        json_response($result);
    }

    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
