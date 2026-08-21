<?php
// /server/api/section-diagrams-reorder.php

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

// The full set of ids in their new left-to-right, top-to-bottom order.
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$ids = $input['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No diagram order specified.']);
    exit;
}

foreach ($ids as $index => $id) {
    SectionDiagram::where('id', (int)$id)->where('company_id', $companyId)->update(['pos_index' => $index]);
}

SectionDiagramsController::logActivity('Reordered section diagrams', 'Questions');

echo json_encode([
    'success' => true,
    'message' => 'Diagram order updated.',
]);
