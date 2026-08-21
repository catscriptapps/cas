<?php
// /server/api/inspection-detail.php

declare(strict_types=1);

use Src\Controller\InspectionsController;
use Src\Controller\InspectionDetailController;
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

$sectionId = $_GET['section_id'] ?? '';
if (!$sectionId) {
    json_response(['success' => false, 'messages' => ['section_id is required.']], 400);
    exit;
}

$controller = new InspectionDetailController();
$controller->sectionContent($inspection, $sectionId);
