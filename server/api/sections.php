<?php
// /server/api/sections.php

declare(strict_types=1);

use Src\Controller\SectionsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// Sections belong to a single company's question bank -- Company Admin only.
if (!AuthService::isCompanyAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new SectionsController();
    $override   = strtoupper($input['_method'] ?? '');

    if ($method === 'POST') {
        if ($override === 'DELETE') {
            $result = $controller->delete($input['id'] ?? null);
        } else {
            $result = $controller->save($input);
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
