<?php
// /server/api/standards.php

declare(strict_types=1);

use Src\Controller\StandardsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// Standards belong to a single company's report boilerplate -- Company Admin only.
if (!AuthService::isCompanyAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new StandardsController();

    if ($method === 'GET') {
        $controller->index();
        exit;
    }

    if ($method === 'POST') {
        $override = strtoupper($input['_method'] ?? '');
        $action = $input['action'] ?? null;

        if ($override === 'DELETE') {
            $result = $controller->delete($input['id'] ?? null);
        } elseif ($action === 'reorder') {
            $result = $controller->reorder($input['ids'] ?? []);
        } else {
            $result = $controller->save($input);
        }

        if (!empty($result['fullHtml'])) {
            $result['fullHtml'] = mb_convert_encoding($result['fullHtml'], 'UTF-8', 'UTF-8');
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
