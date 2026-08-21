<?php
// /server/api/note-hints-delete.php

declare(strict_types=1);

use Src\Controller\NoteHintsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::isCompanyAdmin() && !AuthService::isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'messages' => ['Authentication required.']]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'messages' => ['Method Not Allowed']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$controller = new NoteHintsController();
echo json_encode($controller->delete($input['id'] ?? null));
