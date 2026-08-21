<?php
// /server/api/questions.php

declare(strict_types=1);

use Src\Controller\QuestionsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// Questions belong to a single company's question bank -- Company Admin only.
if (!AuthService::isCompanyAdmin()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new QuestionsController();
    $override   = strtoupper($input['_method'] ?? '');

    if ($method === 'GET') {
        $sectionId = (string)($_GET['section_id'] ?? '');
        if ($sectionId === '') {
            json_response(['success' => false, 'messages' => ['A section is required.']], 400);
        }
        $controller->index($sectionId);
        exit;
    }

    if ($method === 'POST') {
        if ($override === 'DELETE') {
            $result = $controller->delete($input['id'] ?? null);
        } else {
            $result = $controller->save($input);
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
