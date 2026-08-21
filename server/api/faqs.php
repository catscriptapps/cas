<?php
// /server/api/faqs.php

declare(strict_types=1);

use Src\Controller\FaqsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$userId = AuthService::userId();

// 1. Auth Check: Only block if it's NOT a GET request 
// (or if you want the admin search to also be private, we'll keep it)
if (!$userId && $method !== 'GET') {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

try {
    $controller = new FaqsController();

    // HANDLE SEARCH / FETCH (GET)
    if ($method === 'GET') {
        $controller->index();
        exit;
    }

    // HANDLE SAVE/DELETE (POST)
    if ($method === 'POST') {
        // Re-verify auth just to be safe for write operations
        if (!$userId) {
            json_response(['success' => false, 'messages' => ['Unauthorized']], 401);
            exit;
        }

        $override = strtoupper($input['_method'] ?? '');

        if ($override === 'DELETE') {
            $result = $controller->delete($input['id'] ?? 0);
        } else {
            $result = $controller->save($input);
        }

        if (!empty($result['rowHtml'])) {
            $result['rowHtml'] = mb_convert_encoding($result['rowHtml'], 'UTF-8', 'UTF-8');
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
