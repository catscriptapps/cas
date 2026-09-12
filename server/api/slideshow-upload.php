<?php
// /server/api/slideshow-upload.php

declare(strict_types=1);

use Src\Controller\SlideshowController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'message' => 'Admin access required.'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// The shared uploader always posts as the multi-file 'images[]' shape; here
// (unlike sponsors-upload.php) we actually want to process every file in it.
$files = (!empty($_FILES['images']) && is_array($_FILES['images']['tmp_name'] ?? null))
    ? $_FILES['images']
    : ['tmp_name' => [], 'type' => [], 'name' => [], 'error' => [], 'size' => []];

json_response((new SlideshowController())->uploadAndCreate($files));
