<?php
// /server/api/verify-account.php

declare(strict_types=1);

use Src\Controller\VerificationController;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method !== 'POST') {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }

    $controller = new VerificationController();
    json_response($controller->verify((string)($input['email'] ?? ''), (string)($input['token'] ?? '')));
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
