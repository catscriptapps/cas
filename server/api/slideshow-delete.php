<?php
// /server/api/slideshow-delete.php

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

// Always an array -- a single delete just sends one id, so there's only one
// code path to maintain for both the per-tile delete button and bulk-select.
$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image(s) specified.']);
    exit;
}

$baseUploadDir = realpath(__DIR__ . '/../../public/images/home/');
$deletedCount = 0;

foreach ($ids as $id) {
    $slide = Slideshow::find((int)$id);
    if (!$slide) {
        continue;
    }

    $absolutePath = realpath($baseUploadDir . '/' . $slide->image_name);
    if ($absolutePath && strpos($absolutePath, $baseUploadDir) === 0 && file_exists($absolutePath)) {
        @unlink($absolutePath);
    }

    if ($slide->delete()) {
        $deletedCount++;
    }
}

if ($deletedCount === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No matching image(s) found to delete.']);
    exit;
}

SlideshowController::logActivity("Removed {$deletedCount} image(s) from the header slideshow", 'Slideshow');

echo json_encode([
    'success' => true,
    'message' => $deletedCount === 1 ? 'Image deleted successfully.' : "{$deletedCount} images deleted successfully.",
    'deleted' => $deletedCount,
]);
