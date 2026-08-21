<?php
// /server/api/section-diagrams-delete.php

declare(strict_types=1);

use App\Models\SectionDiagram;
use Src\Controller\SectionDiagramsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::isCompanyAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$companyId = AuthService::currentUser()->company_id ?? 0;

// Always an array -- a single delete just sends one id, so there's only one
// code path to maintain for both the per-tile delete button and bulk-select.
$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No diagram(s) specified.']);
    exit;
}

$deletedCount = 0;

foreach ($ids as $id) {
    // Scoped to the signed-in company so one Company Admin can never delete
    // another company's diagram by guessing/tampering with an id.
    $diagram = SectionDiagram::where('id', (int)$id)->where('company_id', $companyId)->first();
    if (!$diagram) {
        continue;
    }

    if ($diagram->delete()) {
        $deletedCount++;
    }
}

if ($deletedCount === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No matching diagram(s) found to delete.']);
    exit;
}

SectionDiagramsController::logActivity("Removed {$deletedCount} section diagram(s)", 'Questions');

echo json_encode([
    'success' => true,
    'message' => $deletedCount === 1 ? 'Diagram deleted successfully.' : "{$deletedCount} diagrams deleted successfully.",
    'deleted' => $deletedCount,
]);
