<?php
// /server/api/sources.php

declare(strict_types=1);

use Src\Controller\SourcesController;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        json_response([
            'success' => true,
            'data' => SourcesController::list(),
        ]);
    }

    json_response([
        'success' => false,
        'messages' => ['Method not allowed']
    ], 405);
} catch (Throwable $e) {
    json_response([
        'success' => false,
        'messages' => ['Server error: ' . $e->getMessage()]
    ], 500);
}
