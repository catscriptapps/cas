<?php
// /server/api/paypal-create-order.php
//
// Public endpoint -- called by the registration wizard's payment step,
// right before rendering the PayPal Smart Buttons.

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
    json_response($controller->createOrder($input));
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}
