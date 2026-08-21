<?php
// /server/api/company-admins.php

declare(strict_types=1);

use Src\Controller\CompanyAdminsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// Fully admin-only, same posture as /api/companies -- managing who can sign
// in as a given company's admin is not something to leave reachable by a
// guest-create path.
if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new CompanyAdminsController();
    $override   = strtoupper($input['_method'] ?? '');

    if ($method === 'GET') {
        $companyId = (string)($_GET['company_id'] ?? '');
        json_response($controller->list($companyId));
        exit;
    }

    if ($method === 'POST') {
        if ($override === 'DELETE') {
            $result = $controller->delete((string)($input['id'] ?? ''));
        } else {
            $result = $controller->create($input);
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
