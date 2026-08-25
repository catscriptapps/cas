<?php
// /server/api/players.php

declare(strict_types=1);

use Src\Controller\PlayersController;

header('Content-Type: application/json; charset=UTF-8');

// PlayersController::index() enforces the login check itself (matches
// every action -- roster/available/add/delete/toggle-goalie -- needing an
// authenticated admin, unlike Schedules/Seasons GET which are public).
$input = array_merge($_GET, json_decode(file_get_contents('php://input'), true) ?: $_POST);

try {
    json_response((new PlayersController())->index($input));
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => ['Server error: ' . $e->getMessage()]], 500);
}
