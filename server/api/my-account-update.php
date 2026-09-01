<?php
// /server/api/my-account-update.php

declare(strict_types=1);

use Src\Controller\MyAccountController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::isRegistrant()) {
    json_response(['success' => false, 'messages' => ['You must be signed in to do that.']], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$entryId = (int)($input['entry_id'] ?? 0);

if ($entryId <= 0) {
    json_response(['success' => false, 'messages' => ['Missing registration id.']], 400);
}

try {
    json_response((new MyAccountController())->updateRegistration($entryId, $input));
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
