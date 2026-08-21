<?php
// /server/api/report-detail.php
//
// Public, unauthenticated counterpart to inspection-detail.php: lets the
// guest access-code report page (resources/views/pages/report/detail.php)
// switch section tabs without a session, by resolving the inspection via
// its access code instead of the current signed-in user. Only ever reaches
// a finalized, non-expired inspection (see findByAccessCode()), so the
// section content it returns is always rendered read-only.

declare(strict_types=1);

use Src\Controller\InspectionsController;
use Src\Controller\InspectionDetailController;

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    exit;
}

$inspection = InspectionsController::findByAccessCode($_GET['code'] ?? null);
if (!$inspection) {
    json_response(['success' => false, 'messages' => ['Report not found.']], 404);
    exit;
}

$sectionId = $_GET['section_id'] ?? '';
if (!$sectionId) {
    json_response(['success' => false, 'messages' => ['section_id is required.']], 400);
    exit;
}

$controller = new InspectionDetailController();
$controller->sectionContent($inspection, $sectionId);
