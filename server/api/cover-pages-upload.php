<?php
// /server/api/cover-pages-upload.php

declare(strict_types=1);

use App\Models\CoverPage;
use Src\Controller\CoverPagesController;
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

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No photos found in request.']);
    exit;
}

// A generous but finite ceiling -- mirrors Slideshow's cap, just per company
// instead of global, since front+back images both draw from this one pool.
$coverPagesLimit = 40;
$existingCount = CoverPage::where('company_id', $companyId)->count();
$incoming = count($_FILES['images']['tmp_name']);

if ($existingCount >= $coverPagesLimit) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => "You have reached the limit of {$coverPagesLimit} cover page images."]);
    exit;
}

if ($existingCount + $incoming > $coverPagesLimit) {
    $allowed = $coverPagesLimit - $existingCount;
    foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $field) {
        $_FILES['images'][$field] = array_slice($_FILES['images'][$field], 0, $allowed);
    }
}

$coverPagesUploadDir = realpath(__DIR__ . '/../../public/images/uploads/') . '/cover-pages/' . $companyId . '/';

$service = new ImageUploadService($coverPagesUploadDir, 2000, 90);

$uploaded = $service->upload($_FILES['images'], function (array $files) use ($companyId) {
    foreach ($files as $key => $fileInfo) {
        $files[$key]['fileUrl'] = '/images/uploads/cover-pages/' . $companyId . '/' . $fileInfo['fileName'];
        $files[$key]['resultUrl'] = $fileInfo['fileName'];
    }
    return $files;
});

if (empty($uploaded) || (isset($uploaded['success']) && $uploaded['success'] === false)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $uploaded['message'] ?? 'Upload failed.']);
    exit;
}

$nextPos = (int)(CoverPage::where('company_id', $companyId)->max('pos_index') ?? -1) + 1;
$responseFiles = [];

foreach ($uploaded as $fileInfo) {
    CoverPage::create([
        'company_id'    => $companyId,
        'image_name'    => $fileInfo['resultUrl'],
        'pos_index'     => $nextPos++,
        'page_location' => CoverPage::LOCATION_FRONT,
    ]);
    $responseFiles[] = ['url' => $fileInfo['fileUrl']];
}

CoverPagesController::logActivity("Added " . count($responseFiles) . " cover page image(s)", 'Cover Pages');

echo json_encode([
    'success' => true,
    'message' => 'Cover page images uploaded successfully.',
    'files'   => $responseFiles,
]);
