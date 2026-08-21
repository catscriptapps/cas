<?php
// /server/api/inspection-library-videos.php
//
// Chunked upload into the video library (no section required any more) and
// hard-delete-from-library. Mirrors the old inspection-video-upload.php /
// inspection-videos.php split, combined into one file since each chunk POST
// and each delete POST are trivially distinguished by their fields.

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

if (!empty($_FILES['video_chunk'])) {
    $inspection = InspectionsController::findForCurrentUser($_POST['inspection_id'] ?? null);
    if (!$inspection) {
        json_response(['success' => false, 'messages' => ['Inspection not found.']], 404);
        exit;
    }

    $chunk = [
        'file' => $_FILES['video_chunk'],
        'uuid' => (string)($_POST['file_uuid'] ?? ''),
        'index' => (int)($_POST['chunk_index'] ?? 0),
        'total' => (int)($_POST['total_chunks'] ?? 1),
        'originalName' => (string)($_POST['filename'] ?? 'video.mp4'),
    ];

    if ($chunk['uuid'] === '') {
        json_response(['success' => false, 'messages' => ['file_uuid is required.']], 400);
        exit;
    }

    $result = $controller->uploadVideoChunk($inspection, $chunk);

    // video-upload-modal.js reads a singular `message` string on failure.
    if (empty($result['success']) && !isset($result['message'])) {
        $result['message'] = $result['messages'][0] ?? 'Upload failed.';
    }

    json_response($result);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$inspection = InspectionsController::findForCurrentUser($input['inspection_id'] ?? null);
if (!$inspection) {
    json_response(['success' => false, 'messages' => ['Inspection not found.']], 404);
    exit;
}

$ids = is_array($input['ids'] ?? null) ? $input['ids'] : [];
if (!empty($ids)) {
    json_response($controller->deleteVideosBulk($inspection, $ids));
}

json_response($controller->deleteVideoFromLibrary($inspection, $input['id'] ?? null));
