<?php
// /server/api/user-types.php

declare(strict_types=1);

use Src\Controller\UserTypesController;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        // Fetches legacy fields: user_type_id and user_type
        $types = UserTypesController::list();

        json_response([
            'success' => true,
            'data' => $types
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
