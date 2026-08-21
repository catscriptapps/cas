<?php
// /server/api/contact.php
declare(strict_types=1);

use Src\Controller\MessagesController;

// 1. Bootstrap once.
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_response(['success' => false, 'message' => 'No data.'], 400);
}

try {
    // 2. ONLY use the controller. 
    // If you still get two entries after this, 
    // the 'bootstrap.php' is definitely the one doing the first save.
    $message = MessagesController::create($input);

    json_response([
        'success' => true,
        'data'    => $message
    ]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}
