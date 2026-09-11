<?php
// /server/api/sponsors.php

declare(strict_types=1);

use Src\Controller\SponsorsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response(['success' => true, 'sponsors' => (new SponsorsController())->getAll()]);
}

if ($method !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
}

if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Admin access required.']], 403);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

json_response((new SponsorsController())->create($input));
