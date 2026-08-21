<?php
// /server/api/inspection-library-video-reorder.php

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

$sectionId = (string)($input['section_id'] ?? '');
if (!$sectionId) {
    json_response(['success' => false, 'messages' => ['section_id is required.']], 400);
    exit;
}

$ids = is_array($input['ids'] ?? null) ? $input['ids'] : [];

$controller = new InspectionLibraryController();
json_response($controller->reorderVideoSection($inspection, $sectionId, $ids));
