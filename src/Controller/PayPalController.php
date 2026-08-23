<?php
// /src/Controller/PayPalController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Registration;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\PayPalService;

/**
 * Drives the PayPal Smart Buttons flow for a single registration: create an
 * Order once the registrant reaches the payment step, capture it once
 * they've approved it in the PayPal popup, and (as a reliability backstop
 * the legacy essahockey site never had) confirm the same capture again via
 * a verified PayPal webhook in case the browser-side capture call never
 * lands.
 *
 * A registration's `paypal_order_id` column -- not the PayPal
 * `reference_id` field -- is the lookup key used to match a capture back to
 * its registration; `reference_id` is still sent for visibility on
 * PayPal's side, but isn't relied on for correctness.
 */
class PayPalController
{
    use RecentActivityLogger;

    /**
     * Step 1: create a PayPal Order for a pending registration.
     */
    public function createOrder(array $data): array
    {
        try {
            $encodedId = (string)($data['encoded_id'] ?? '');
            $registrationId = IdEncoder::decode($encodedId);
            $registration = $registrationId ? Registration::with('division')->find($registrationId) : null;

            if (!$registration) {
                throw new \Exception('Registration not found.');
            }
            if ($registration->has_paid) {
                throw new \Exception('This registration has already been paid.');
            }
            if (!$registration->division) {
                throw new \Exception('This registration is missing its division.');
            }

            $order = PayPalService::createOrder(
                $encodedId,
                (float)$registration->division->price,
                trim(($_ENV['APP_NAME'] ?? 'Canadian All Star Sports') . ' - ' . $registration->division->division)
            );

            if (empty($order['id'])) {
                throw new \Exception('PayPal did not return an order id.');
            }

            $registration->paypal_order_id = $order['id'];
            $registration->save();

            return ['success' => true, 'order_id' => $order['id']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Step 2: capture an approved order (browser-triggered, right after the
     * PayPal popup's onApprove callback fires).
     */
    public function captureOrder(array $data): array
    {
        try {
            $orderId = (string)($data['order_id'] ?? '');
            if ($orderId === '') {
                throw new \Exception('Missing order id.');
            }

            $capture = PayPalService::captureOrder($orderId);

            return $this->applyCapture($capture);
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Step 3 (backstop): PayPal's own server calls this after a capture
     * completes, independent of whether the browser's captureOrder() call
     * above ever ran. $event is the already-signature-verified webhook
     * payload.
     */
    public function handleWebhook(array $event): array
    {
        try {
            $eventType = $event['event_type'] ?? '';

            if ($eventType !== 'PAYMENT.CAPTURE.COMPLETED') {
                // Not an event we act on -- acknowledge so PayPal stops retrying it.
                return ['success' => true, 'ignored' => true];
            }

            $resource = $event['resource'] ?? [];
            $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

            if (!$orderId) {
                throw new \Exception('Webhook payload missing order id.');
            }

            // Re-fetch the full order from PayPal rather than trusting the
            // webhook body's own capture amount/status at face value.
            $order = PayPalService::getOrder($orderId);

            return $this->applyCapture($order, $resource['id'] ?? null);
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Shared logic: given a captured/fetched Order payload, find the
     * matching registration (by paypal_order_id) and mark it paid --
     * idempotent on transaction_id, so a capture call and a later webhook
     * confirming the same payment don't double-process it.
     */
    private function applyCapture(array $order, ?string $fallbackTransactionId = null): array
    {
        $orderId = $order['id'] ?? null;
        $status = $order['status'] ?? null;

        if (!$orderId) {
            throw new \Exception('PayPal response is missing an order id.');
        }

        $registration = Registration::where('paypal_order_id', $orderId)->first();
        if (!$registration) {
            throw new \Exception('No registration matches this PayPal order.');
        }

        $capture = $order['purchase_units'][0]['payments']['captures'][0] ?? null;
        $transactionId = $capture['id'] ?? $fallbackTransactionId;
        $captureStatus = $capture['status'] ?? $status;
        $amount = $capture['amount']['value'] ?? null;

        if ($registration->has_paid && $registration->transaction_id === $transactionId) {
            // Already processed -- capture call and webhook both landed.
            return ['success' => true, 'already_processed' => true, 'encoded_id' => IdEncoder::encode($registration->entry_id)];
        }

        if ($status !== 'COMPLETED' && $captureStatus !== 'COMPLETED') {
            throw new \Exception('Payment was not completed.');
        }

        $registration->has_paid = true;
        $registration->amount_paid = $amount !== null ? (float)$amount : $registration->amount_paid;
        $registration->transaction_id = $transactionId;
        $registration->save();

        static::logActivity(
            "Payment received for registration: {$registration->full_name}",
            'Registrations',
            $registration->entry_id
        );

        return [
            'success' => true,
            'encoded_id' => IdEncoder::encode($registration->entry_id),
            'full_name' => $registration->full_name,
            'amount_paid' => (float)$registration->amount_paid,
        ];
    }
}
