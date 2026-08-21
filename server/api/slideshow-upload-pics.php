<?php
// /server/api/slideshow-upload-pics.php

declare(strict_types=1);

use App\Models\Slideshow;
use Src\Controller\SlideshowController;
use Src\Service\AuthService;
use Src\Service\ImageUploadService;

header('Content-Type: application/json');

if (!AuthService::currentUser()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No photos found in request.']);
    exit;
}

// The global header slideshow isn't tied to a single owner's gallery, so it
// gets a much higher ceiling than the per-property media limit.
$slideshowLimit = 60;
$existingCount = Slideshow::count();
$incoming = count($_FILES['images']['tmp_name']);

if ($existingCount >= $slideshowLimit) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => "You have reached the limit of {$slideshowLimit} slideshow images."]);
    exit;
}

if ($existingCount + $incoming > $slideshowLimit) {
    $allowed = $slideshowLimit - $existingCount;
    foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $field) {
        $_FILES['images'][$field] = array_slice($_FILES['images'][$field], 0, $allowed);
    }
}

$homeUploadDir = realpath(__DIR__ . '/../../public/images/home/');

$service = new ImageUploadService($homeUploadDir, 2000, 90);

$uploaded = $service->upload($_FILES['images'], function (array $files) {
    foreach ($files as $key => $fileInfo) {
        $files[$key]['fileUrl'] = '/images/home/' . $fileInfo['fileName'];
        $files[$key]['resultUrl'] = $fileInfo['fileName'];
    }
    return $files;
});

if (empty($uploaded) || (isset($uploaded['success']) && $uploaded['success'] === false)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $uploaded['message'] ?? 'Upload failed.']);
    exit;
}

$nextPos = (int)(Slideshow::max('pos_index') ?? -1) + 1;
$responseFiles = [];

foreach ($uploaded as $fileInfo) {
    Slideshow::create([
        'image_name' => $fileInfo['resultUrl'],
        'pos_index'  => $nextPos++,
        'status_id'  => 1,
    ]);
    $responseFiles[] = ['url' => $fileInfo['fileUrl']];
}

SlideshowController::logActivity("Added " . count($responseFiles) . " image(s) to the header slideshow", 'Slideshow');

echo json_encode([
    'success' => true,
    'message' => 'Slideshow images uploaded successfully.',
    'files'   => $responseFiles,
]);
