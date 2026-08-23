<?php
// /server/api/paypal-webhook.php
//
// Server-to-server endpoint configured in the PayPal Developer Dashboard
// (Sandbox/Live app -> Webhooks -> add this URL, subscribed to at least
// "Payment capture completed"). This is the reliability backstop the
// legacy essahockey integration never had: even if the shopper closes the
// tab right after approving payment (so the browser's own capture-order
// call never fires), PayPal still confirms the payment here.
//
// Every request is signature-verified via PayPalService before anything in
// its body is trusted -- see PayPalService::verifyWebhookSignature().

declare(strict_types=1);

use Src\Controller\PayPalController;
use Src\Service\PayPalService;

header('Content-Type: application/json; charset=UTF-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
}

$rawBody = file_get_contents('php://input');
$headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];

try {
    if (!PayPalService::verifyWebhookSignature($headers, $rawBody)) {
        json_response(['success' => false, 'messages' => ['Invalid webhook signature']], 400);
    }

    $event = json_decode($rawBody, true) ?: [];

    $controller = new PayPalController();
    json_response($controller->handleWebhook($event));
} catch (Throwable $e) {
    // PayPal retries on non-2xx, so surface real failures as 500 rather
    // than swallowing them -- but never leak the exception message to a
    // caller we haven't authenticated.
    error_log('PayPal webhook error: ' . $e->getMessage());
    json_response(['success' => false, 'messages' => ['Webhook processing error']], 500);
}
