<?php
// /server/api/inspection-library-pictures.php
//
// Upload into the photo library (multipart, no section required any more)
// and hard-delete-from-library. Mirrors the old inspection-pictures.php's
// upload/delete split.

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

$controller = new InspectionLibraryController();

if (!empty($_FILES['images']) && !empty($_FILES['images']['tmp_name'][0])) {
    $inspectionId = $_POST['inspection_id'] ?? $_GET['inspection_id'] ?? null;
    $inspection = InspectionsController::findForCurrentUser($inspectionId);
    if (!$inspection) {
        json_response(['success' => false, 'messages' => ['Inspection not found.']], 404);
        exit;
    }

    json_response($controller->uploadPictures($inspection, $_FILES['images']));
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$inspection = InspectionsController::findForCurrentUser($input['inspection_id'] ?? null);
if (!$inspection) {
    json_response(['success' => false, 'messages' => ['Inspection not found.']], 404);
    exit;
}

$ids = is_array($input['ids'] ?? null) ? $input['ids'] : [];
if (!empty($ids)) {
    json_response($controller->deletePicturesBulk($inspection, $ids));
}

json_response($controller->deletePictureFromLibrary($inspection, $input['id'] ?? null));
