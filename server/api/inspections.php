<?php
// /server/api/inspections.php

declare(strict_types=1);

use Src\Controller\InspectionsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// Inspections are readable by Company Admin (whole company) and Inspector
// (their own inspections only, scoped in InspectionsController::index()).
if (!AuthService::isCompanyAdmin() && !AuthService::isInspector() && !AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new InspectionsController();

    if ($method === 'GET') {
        $controller->index();
        exit;
    }

    if ($method === 'POST') {
        $override = strtoupper($input['_method'] ?? '');
        $action = $input['action'] ?? null;

        if ($override === 'DELETE') {
            $result = $controller->delete($input['id'] ?? null);
        } elseif ($action === 'finalize') {
            $result = $controller->finalize($input['id'] ?? null);
        } elseif ($action === 'reopen') {
            $result = $controller->reopen($input['id'] ?? null);
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
