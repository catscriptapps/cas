<?php
// /server/api/companies.php

declare(strict_types=1);

use Src\Controller\CompaniesController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// Companies are readable by a Company Admin (scoped to their own company by
// CompaniesController::index()). Writes: a Company Admin may edit their own
// company (enforced again inside CompaniesController::save(), not just
// here), but creating a company or deleting one is the global Admin only --
// unlike Users, there's no guest registration path here.
$isAdmin = AuthService::isAdmin();
if (!$isAdmin && !AuthService::isCompanyAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new CompaniesController();
    $override   = strtoupper($input['_method'] ?? '');

    if ($method === 'GET') {
        $controller->index();
        exit;
    }

    if ($method === 'POST') {
        $isDelete = ($override === 'DELETE');

        if ($isDelete) {
            if (!$isAdmin) {
                json_response(['success' => false, 'messages' => ['Admin access required']], 403);
                exit;
            }
            $result = $controller->delete($input['id'] ?? 0);
        } else {
            $result = $controller->save($input);
        }

        if (!empty($result['rowHtml'])) {
            $result['rowHtml'] = mb_convert_encoding($result['rowHtml'], 'UTF-8', 'UTF-8');
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
