<?php
// /server/api/company-logo-delete.php

declare(strict_types=1);

use App\Models\Company;
use App\Utils\IdEncoder;
use Src\Service\AuthService;
use Src\Controller\CompaniesController;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (!AuthService::isAdmin() && !AuthService::isCompanyAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: not logged in.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$encodedCompanyId = $input['id'] ?? '';

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

$response = ['success' => false, 'message' => 'Failed to delete logo.'];

$baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/');
if (!$baseUploadDir) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Base upload directory not found for security check.'
    ]);
    exit;
}

$LOGO_DIR_PREFIX = 'images/uploads/company-logos/';

try {
    if (empty($company->logo_url)) {
        $response['success'] = true;
        $response['message'] = 'No logo currently set.';
        http_response_code(200);
        echo json_encode($response);
        exit;
    }

    $fullLogoPath = $LOGO_DIR_PREFIX . $company->logo_url;
    $oldPath = realpath(__DIR__ . '/../../public/' . $fullLogoPath);

    $deletionSuccess = false;

    if (
        $oldPath &&
        strpos($oldPath, $baseUploadDir) === 0 &&
        file_exists($oldPath)
    ) {
        if (unlink($oldPath)) {
            $deletionSuccess = true;
        } else {
            error_log("Failed to delete company logo file: " . $oldPath);
            $response['message'] = 'File exists but could not be deleted (permission issue).';
            http_response_code(500);
            echo json_encode($response);
            exit;
        }
    } else {
        $deletionSuccess = true;
        error_log("Old company logo file not found on disk or failed path validation: " . $fullLogoPath);
    }

    if ($deletionSuccess) {
        $company->logo_url = null;
        $company->save();

        CompaniesController::logActivity("Removed logo for company: {$company->company_name}", 'Companies');

        $response['success'] = true;
        $response['message'] = 'Logo successfully deleted.';
        http_response_code(200);
    }
} catch (\Throwable $e) {
    error_log('Company logo deletion failed: ' . $e->getMessage());

    http_response_code(500);
    $response['message'] = 'A server error occurred during logo deletion.';
}

echo json_encode($response);
exit;
