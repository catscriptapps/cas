<?php
// /server/api/section-diagrams-upload.php
//
// The generic upload modal (resources/js/modals/upload-modal.js) only ever
// sends `images[]` blobs -- no room for extra POST fields -- so section_id
// rides the endpoint URL as a query param instead, same pattern as
// server/api/inspection-pictures.php.

declare(strict_types=1);

use App\Models\Section;
use App\Models\SectionDiagram;
use App\Utils\IdEncoder;
use Src\Controller\SectionDiagramsController;
use Src\Service\AuthService;
use Src\Service\ImageUploadService;

header('Content-Type: application/json');

if (!AuthService::isCompanyAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$companyId = AuthService::currentUser()->company_id ?? 0;
if (!$companyId) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'No company associated with this account.']);
    exit;
}

$sectionId = IdEncoder::decode((string)($_GET['section_id'] ?? ''));
$section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $companyId)->first() : null;
if (!$section) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Section not found.']);
    exit;
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No diagrams found in request.']);
    exit;
}

// A generous but finite ceiling, matching Cover Pages' cap -- per section
// rather than per company, since each section's diagrams are independent.
$diagramsLimit = 40;
$existingCount = SectionDiagram::where('company_id', $companyId)->where('section_id', $sectionId)->count();
$incoming = count($_FILES['images']['tmp_name']);

if ($existingCount >= $diagramsLimit) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => "You have reached the limit of {$diagramsLimit} diagrams for this section."]);
    exit;
}

if ($existingCount + $incoming > $diagramsLimit) {
    $allowed = $diagramsLimit - $existingCount;
    foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $field) {
        $_FILES['images'][$field] = array_slice($_FILES['images'][$field], 0, $allowed);
    }
}

$diagramsUploadDir = realpath(__DIR__ . '/../../public/images/uploads/') . '/section-diagrams/' . $companyId . '/' . $sectionId . '/';

$service = new ImageUploadService($diagramsUploadDir, 2000, 90);

$uploaded = $service->upload($_FILES['images'], function (array $files) use ($companyId, $sectionId) {
    foreach ($files as $key => $fileInfo) {
        $files[$key]['fileUrl'] = "/images/uploads/section-diagrams/{$companyId}/{$sectionId}/" . $fileInfo['fileName'];
        $files[$key]['resultUrl'] = $fileInfo['fileName'];
    }
    return $files;
});

if (empty($uploaded) || (isset($uploaded['success']) && $uploaded['success'] === false)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $uploaded['message'] ?? 'Upload failed.']);
    exit;
}

$nextPos = (int)(SectionDiagram::where('company_id', $companyId)->where('section_id', $sectionId)->max('pos_index') ?? -1) + 1;
$responseFiles = [];

foreach ($uploaded as $fileInfo) {
    SectionDiagram::create([
        'company_id' => $companyId,
        'section_id' => $sectionId,
        'image_name' => $fileInfo['resultUrl'],
        'pos_index'  => $nextPos++,
    ]);
    $responseFiles[] = ['url' => $fileInfo['fileUrl']];
}

SectionDiagramsController::logActivity("Added " . count($responseFiles) . " diagram(s) to section: {$section->name}", 'Questions');

echo json_encode([
    'success' => true,
    'message' => 'Diagrams uploaded successfully.',
    'files'   => $responseFiles,
]);
