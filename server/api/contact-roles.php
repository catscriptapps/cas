<?php
// /server/api/contact-roles.php

declare(strict_types=1);

use Src\Controller\ContactsController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

if (!AuthService::userId()) {
    json_response(['success' => false, 'messages' => ['Authentication required']], 401);
}

try {
    json_response(['success' => true, 'data' => ContactsController::roles()]);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
