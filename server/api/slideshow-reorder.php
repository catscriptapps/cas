<?php
// /server/api/slideshow-reorder.php

declare(strict_types=1);

use App\Models\Slideshow;
use Src\Controller\SlideshowController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::currentUser()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// The full set of ids in their new left-to-right, top-to-bottom order.
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$ids = $input['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image order specified.']);
    exit;
}

foreach ($ids as $index => $id) {
    Slideshow::where('id', (int)$id)->update(['pos_index' => $index]);
}

SlideshowController::logActivity('Reordered header slideshow images', 'Slideshow');

echo json_encode([
    'success' => true,
    'message' => 'Slideshow order updated.',
]);
