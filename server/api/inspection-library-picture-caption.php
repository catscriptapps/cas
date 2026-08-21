<?php
// /server/api/inspection-library-picture-caption.php
//
// Saves a picture's caption scoped to one section, or to Contracts --
// captions live on the junction row, not the library picture itself, since
// the same photo can carry a different caption in each place it's assigned.

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
        ? $controller->saveContractCaption($inspection, $input)
        : $controller->savePictureSectionCaption($inspection, $input)
);
