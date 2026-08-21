<?php
// /server/api/section-diagrams.php

declare(strict_types=1);

use App\Models\SectionDiagram;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

header('Content-Type: application/json');

// Section Diagrams belong to a single company's question bank, scoped to
// one Section -- Company Admin only, same as Questions and Cover Pages.
if (!AuthService::isCompanyAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$companyId = AuthService::currentUser()->company_id ?? 0;
$sectionId = IdEncoder::decode((string)($_GET['section_id'] ?? ''));

if (!$sectionId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A section is required.']);
    exit;
}

$diagrams = SectionDiagram::where('company_id', $companyId)
    ->where('section_id', $sectionId)
    ->orderBy('pos_index', 'asc')
    ->get(['id', 'image_name', 'caption'])
    ->map(function (SectionDiagram $diagram) use ($companyId, $sectionId) {
        return [
            'id'         => $diagram->id,
            'image_name' => $diagram->image_name,
            'caption'    => $diagram->caption,
            'image_url'  => "images/uploads/section-diagrams/{$companyId}/{$sectionId}/{$diagram->image_name}",
        ];
    });

echo json_encode(['success' => true, 'diagrams' => $diagrams]);
