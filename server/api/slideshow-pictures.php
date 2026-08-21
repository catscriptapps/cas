<?php
// /server/api/slideshow-pictures.php

declare(strict_types=1);

use App\Models\Slideshow;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::currentUser()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$pictures = Slideshow::orderBy('pos_index', 'asc')->get(['id', 'image_name']);

echo json_encode(['success' => true, 'pictures' => $pictures]);
