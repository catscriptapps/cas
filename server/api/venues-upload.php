<?php
// /server/api/venues-upload.php

declare(strict_types=1);

use Src\Controller\VenuesController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'message' => 'Admin access required.'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// The shared uploader (resources/js/modals/upload-modal.js) always posts as
// the multi-file 'images[]' shape, even in single-file mode.
$file = null;
if (!empty($_FILES['images']) && is_array($_FILES['images']['tmp_name'] ?? null)) {
    $file = [
        'tmp_name' => $_FILES['images']['tmp_name'][0] ?? null,
        'type'     => $_FILES['images']['type'][0] ?? '',
        'name'     => $_FILES['images']['name'][0] ?? '',
        'error'    => $_FILES['images']['error'][0] ?? UPLOAD_ERR_NO_FILE,
        'size'     => $_FILES['images']['size'][0] ?? 0,
    ];
}

json_response((new VenuesController())->uploadImage($file));
