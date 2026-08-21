<?php
// /server/api/note-hints.php

declare(strict_types=1);

use Src\Controller\NoteHintsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

// Anyone who can fill out an inspection can browse a hints pool -- writes
// (save/delete) are the ones gated to Company Admin/Admin.
if (!AuthService::isCompanyAdmin() && !AuthService::isInspector() && !AuthService::isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'messages' => ['Authentication required.']]);
    exit;
}

$controller = new NoteHintsController();
echo json_encode($controller->index($_GET['type'] ?? null, $_GET['section_id'] ?? null));
