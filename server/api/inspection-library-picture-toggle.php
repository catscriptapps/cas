<?php
// /server/api/inspection-library-picture-toggle.php
//
// One POST per checkbox toggle in the Photo Library grid -- assigns or
// unassigns a photo to a section, or to Contracts. Fires immediately on
// click (no batch save), matching legacy's section_allocation() endpoint.

declare(strict_types=1);

use Src\Controller\InspectionsController;
use Src\Controller\InspectionLibraryController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::isCompanyAdmin() && !AuthService::isInspector() && !AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$inspection = InspectionsController::findForCurrentUser($input['inspection_id'] ?? null);
if (!$inspection) {
    json_response(['success' => false, 'messages' => ['Inspection not found.']], 404);
    exit;
}

$controller = new InspectionLibraryController();
$target = (string)($input['target'] ?? 'section');

json_response(
    $target === 'contract'
        ? $controller->togglePictureContract($inspection, $input)
        : $controller->togglePictureSection($inspection, $input)
);
