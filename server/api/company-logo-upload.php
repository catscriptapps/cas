<?php
// /server/api/company-logo-upload.php

declare(strict_types=1);

use App\Models\Company;
use App\Utils\IdEncoder;
use Src\Service\AuthService;
use Src\Service\ImageUploadService;
use Src\Controller\CompaniesController;

header('Content-Type: application/json');

// --------------------------------------------------
// Auth: Admin can manage any company; a Company Admin
// only their own.
// --------------------------------------------------
if (!AuthService::isAdmin() && !AuthService::isCompanyAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$encodedCompanyId = $_GET['company_id'] ?? '';
$companyId = IdEncoder::decode((string)$encodedCompanyId);
$company = $companyId ? Company::find($companyId) : null;

if (!$company) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Company not found.']);
    exit;
}

if (!AuthService::isAdmin()) {
    $currentUser = AuthService::currentUser();
    if ((int)($currentUser->company_id ?? 0) !== (int)$company->id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to modify this company.']);
        exit;
    }
}

// --------------------------------------------------
// Validate uploaded file
// --------------------------------------------------
if (
    empty($_FILES['images']) ||
    !is_array($_FILES['images']['tmp_name']) ||
    empty($_FILES['images']['tmp_name'][0])
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No logo image found in request.'
    ]);
    exit;
}

// --------------------------------------------------
// Resolve upload directories
// --------------------------------------------------
$baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/');
if (!$baseUploadDir) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Base upload directory not found.'
    ]);
    exit;
}

$logoUploadDir = $baseUploadDir . '/company-logos/';
if (!is_dir($logoUploadDir)) {
    mkdir($logoUploadDir, 0777, true);
}

// --------------------------------------------------
// HARD single-file enforcement (like avatars)
// --------------------------------------------------
$singleFile = [
    'name'      => [$_FILES['images']['name'][0]],
    'type'      => [$_FILES['images']['type'][0]],
    'tmp_name'  => [$_FILES['images']['tmp_name'][0]],
    'error'     => [$_FILES['images']['error'][0]],
    'size'      => [$_FILES['images']['size'][0]],
];

// --------------------------------------------------
// Upload image
// --------------------------------------------------
$service = new ImageUploadService($logoUploadDir, 2000, 90);

$relativePublicPathPrefix = 'images/uploads/company-logos/';

$uploaded = $service->upload($singleFile, function (array $files) use ($relativePublicPathPrefix) {
    foreach ($files as $key => $fileInfo) {
        $publicUrlWithDir = $relativePublicPathPrefix . $fileInfo['fileName'];

        // Save ONLY the filename to the database via 'resultUrl'
        $files[$key]['resultUrl'] = $fileInfo['fileName'];
        $files[$key]['fileUrl'] = $publicUrlWithDir;
    }

    return $files;
});

if (
    empty($uploaded) ||
    (isset($uploaded['success']) && $uploaded['success'] === false)
) {
    $message = $uploaded['message'] ?? 'Logo upload failed.';
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// --------------------------------------------------
// Persist logo to DB
// --------------------------------------------------
try {
    $newFileName = basename($uploaded[0]['resultUrl']);

    // Delete old logo if one exists
    if (!empty($company->logo_url)) {
        $oldFileNameOnly = basename($company->logo_url);
        $oldFilePath = $relativePublicPathPrefix . $oldFileNameOnly;

        $oldPath = realpath(__DIR__ . '/../../public/' . $oldFilePath);
        if (
            $oldPath &&
            strpos($oldPath, realpath($baseUploadDir)) === 0 &&
            file_exists($oldPath)
        ) {
            unlink($oldPath);
        }
    }

    $company->logo_url = $newFileName;
    $company->save();

    CompaniesController::logActivity("Updated logo for company: {$company->company_name}", 'Companies');
} catch (\Throwable $e) {
    error_log('Company logo DB update failed: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Logo uploaded but database update failed.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Logo uploaded successfully.',
    'files' => [
        [
            'url' => $uploaded[0]['fileUrl']
        ]
    ]
]);
