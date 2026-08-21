<?php
// /server/api/cover-pages.php

declare(strict_types=1);

use App\Models\CoverPage;
use Src\Service\AuthService;

header('Content-Type: application/json');

// Cover Pages belong to a single company's report gallery -- Company Admin only.
if (!AuthService::isCompanyAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$companyId = AuthService::currentUser()->company_id ?? 0;

$coverPages = CoverPage::where('company_id', $companyId)
    ->orderBy('pos_index', 'asc')
    ->get(['id', 'image_name', 'page_location'])
    ->map(function (CoverPage $page) use ($companyId) {
        return [
            'id'            => $page->id,
            'image_name'    => $page->image_name,
            'page_location' => $page->page_location,
            'image_url'     => "images/uploads/cover-pages/{$companyId}/{$page->image_name}",
        ];
    });

echo json_encode(['success' => true, 'pages' => $coverPages]);
