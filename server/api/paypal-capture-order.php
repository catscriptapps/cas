<?php
// /server/api/paypal-capture-order.php
//
// Public endpoint -- called by the PayPal Smart Buttons' onApprove callback
// right after the shopper approves the payment in the popup.

declare(strict_types=1);

use Src\Controller\PayPalController;

header('Content-Type: application/json; charset=UTF-8');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method !== 'POST') {
        json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
    }

    $controller = new PayPalController();
    json_response($controller->captureOrder($input));
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
