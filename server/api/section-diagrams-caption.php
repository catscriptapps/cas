<?php
// /server/api/section-diagrams-caption.php
//
// Caption is an admin-facing organizational note only (e.g. "2nd floor
// furnace wiring") -- it doesn't get printed on the PDF's full-bleed
// diagram page, same as Cover Pages carry no caption at all.

declare(strict_types=1);

use App\Models\SectionDiagram;
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
$diagram = $id ? SectionDiagram::where('id', $id)->where('company_id', $companyId)->first() : null;

if (!$diagram) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Diagram not found.']);
    exit;
}

$diagram->caption = trim((string)($input['caption'] ?? '')) ?: null;
$diagram->save();

echo json_encode(['success' => true, 'message' => 'Saved.']);
