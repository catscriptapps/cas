<?php
// /server/api/sponsors-delete.php

declare(strict_types=1);

use Src\Controller\SponsorsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Admin access required.']], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = (string)($input['id'] ?? '');

json_response((new SponsorsController())->delete($id));
