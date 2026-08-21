<?php
// /server/api/inspection-library-content.php
//
// AJAX tab-switch for the three virtual tabs (Photo Library, Video Library,
// Contracts) -- same shape as inspection-detail.php's real-section
// equivalent, dispatched by tab key instead of a section id.

declare(strict_types=1);

use Src\Controller\InspectionsController;
use Src\Controller\InspectionLibraryController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::isCompanyAdmin() && !AuthService::isInspector() && !AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    exit;
}

$inspection = InspectionsController::findForCurrentUser($_GET['inspection_id'] ?? null);
if (!$inspection) {
    json_response(['success' => false, 'messages' => ['Inspection not found.']], 404);
    exit;
}

$tab = (string)($_GET['tab'] ?? '');

$controller = new InspectionLibraryController();
json_response($controller->content($inspection, $tab));
