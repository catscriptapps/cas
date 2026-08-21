<?php
// /server/api/cover-pages-reorder.php

declare(strict_types=1);

use App\Models\CoverPage;
use Src\Controller\CoverPagesController;
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
    echo json_encode(['success' => false, 'message' => 'No image order specified.']);
    exit;
}

foreach ($ids as $index => $id) {
    CoverPage::where('id', (int)$id)->where('company_id', $companyId)->update(['pos_index' => $index]);
}

CoverPagesController::logActivity('Reordered cover page images', 'Cover Pages');

echo json_encode([
    'success' => true,
    'message' => 'Cover page order updated.',
]);
