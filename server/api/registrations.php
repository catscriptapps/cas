<?php
// /server/api/registrations.php

declare(strict_types=1);

use Src\Controller\RegistrationsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new RegistrationsController();
    $override   = strtoupper($input['_method'] ?? '');
    $userId     = AuthService::userId();

    if ($method === 'GET') {
        if (!$userId) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
            exit;
        }
        $controller->index();
        exit;
    }

    if ($method === 'POST') {
        if (!$userId) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
            exit;
        }

        $isDelete = ($override === 'DELETE');
        $result = $isDelete ? $controller->delete($input['id'] ?? 0) : $controller->save($input);

        if (!empty($result['rowHtml'])) {
            $result['rowHtml'] = mb_convert_encoding($result['rowHtml'], 'UTF-8', 'UTF-8');
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
