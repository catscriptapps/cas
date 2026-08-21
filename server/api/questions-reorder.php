<?php
// /server/api/questions-reorder.php

declare(strict_types=1);

use Src\Controller\QuestionsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::isCompanyAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$ids = $input['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No question order specified.']);
    exit;
}

$controller = new QuestionsController();
echo json_encode($controller->reorder($ids));
