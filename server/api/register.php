<?php
// /server/api/register.php
//
// Public endpoint (no login required) -- creates a pending registration row
// from the guest-facing registration wizard's final step.

declare(strict_types=1);

use Src\Controller\RegisterController;

header('Content-Type: application/json; charset=UTF-8');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method !== 'POST') {
        json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
    }

    $controller = new RegisterController();
    json_response($controller->create($input));
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
