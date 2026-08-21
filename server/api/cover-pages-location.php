<?php
// /server/api/cover-pages-location.php
//
// Toggles a single cover page image between the Front and Back group --
// mirrors legacy's ch_cover_page_location(). This is the only "edit" a
// cover page image has; there's no title/caption field.

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

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = (int)($input['id'] ?? 0);
$location = (int)($input['page_location'] ?? 0);

if (!in_array($location, [CoverPage::LOCATION_FRONT, CoverPage::LOCATION_BACK], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid page location.']);
    exit;
}

$coverPage = CoverPage::where('id', $id)->where('company_id', $companyId)->first();
if (!$coverPage) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Cover page not found.']);
    exit;
}

$coverPage->page_location = $location;
$coverPage->save();

CoverPagesController::logActivity('Updated cover page location (Front/Back)', 'Cover Pages');

echo json_encode(['success' => true, 'message' => 'Cover page updated.']);
