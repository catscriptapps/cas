<?php
// /server/api/stats-update.php

declare(strict_types=1);

use Src\Controller\StatsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::userId()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

try {
    json_response((new StatsController())->save($input));
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
