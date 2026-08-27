<?php
// /server/api/home-page-image-upload.php

declare(strict_types=1);

use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'message' => 'Admin access required.'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];
$maxBytes = 8 * 1024 * 1024; // 8MB -- generous enough for a full-screen screenshot

$file = $_FILES['image'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
    json_response(['success' => false, 'message' => 'No image was received.'], 400);
}

if ($file['size'] > $maxBytes) {
    json_response(['success' => false, 'message' => 'Image is too large (8MB max).'], 400);
}

// Sniff the real content, not the client-supplied MIME string, since that's
// user-controlled and easily spoofed.
$imageInfo = @getimagesize($file['tmp_name']);
$mime = $imageInfo['mime'] ?? null;
if (!$mime || !isset($allowedMimes[$mime])) {
    json_response(['success' => false, 'message' => 'Unsupported image type. Use JPEG, PNG, GIF, or WebP.'], 400);
}

$uploadDir = __DIR__ . '/../../public/images/uploads/home-page/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    json_response(['success' => false, 'message' => 'Could not create the upload directory.'], 500);
}

$filename = 'mission-' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
$destination = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_response(['success' => false, 'message' => 'Failed to save the uploaded image.'], 500);
}

// Relative to /public -- the client prepends its own assetBase, since that
// differs between environments (bare "/" locally, "/cas/" in production).
json_response(['success' => true, 'url' => 'images/uploads/home-page/' . $filename]);
