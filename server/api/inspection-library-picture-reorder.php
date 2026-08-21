<?php
// /server/api/inspection-library-picture-reorder.php
//
// Persists the new visual order of a section's photo grid, or the
// Contracts grid -- position lives on the junction row, independent per
// section/contracts since the same photo can sit in a different spot in
// each place it's assigned.

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

$ids = is_array($input['ids'] ?? null) ? $input['ids'] : [];
$target = (string)($input['target'] ?? 'section');

$controller = new InspectionLibraryController();

if ($target === 'contract') {
    json_response($controller->reorderContracts($inspection, $ids));
}

$sectionId = (string)($input['section_id'] ?? '');
if (!$sectionId) {
    json_response(['success' => false, 'messages' => ['section_id is required.']], 400);
    exit;
}

json_response($controller->reorderPictureSection($inspection, $sectionId, $ids));
