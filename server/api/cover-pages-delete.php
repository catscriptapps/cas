<?php
// /server/api/cover-pages-delete.php

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

// Always an array -- a single delete just sends one id, so there's only one
// code path to maintain for both the per-tile delete button and bulk-select.
$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image(s) specified.']);
    exit;
}

$deletedCount = 0;

foreach ($ids as $id) {
    // Scoped to the signed-in company so one Company Admin can never delete
    // another company's cover page by guessing/tampering with an id.
    $coverPage = CoverPage::where('id', (int)$id)->where('company_id', $companyId)->first();
    if (!$coverPage) {
        continue;
    }

    if ($coverPage->delete()) {
        $deletedCount++;
    }
}

if ($deletedCount === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No matching image(s) found to delete.']);
    exit;
}

CoverPagesController::logActivity("Removed {$deletedCount} cover page image(s)", 'Cover Pages');

echo json_encode([
    'success' => true,
    'message' => $deletedCount === 1 ? 'Image deleted successfully.' : "{$deletedCount} images deleted successfully.",
    'deleted' => $deletedCount,
]);
