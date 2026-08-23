<?php
// /src/Service/PayPalService.php

declare(strict_types=1);

namespace Src\Service;

/**
 * PayPalService
 *
 * Thin wrapper around PayPal's REST v2 Orders API (Smart Buttons / Checkout
 * SDK flow), the same integration style the legacy essahockey registration
 * site uses -- but with credentials read from the environment instead of
 * hardcoded in a web-servable PHP file, and with webhook signature
 * verification added so a payment can be confirmed server-to-server, not
 * only via the browser's own "capture" call after approval.
 *
 * Env vars (see .env):
 *   PAYPAL_MODE            'sandbox' or 'live'
 *   PAYPAL_CLIENT_ID
 *   PAYPAL_CLIENT_SECRET
 *   PAYPAL_WEBHOOK_ID      from the PayPal Developer Dashboard's webhook config
 *   PAYPAL_CURRENCY        defaults to CAD
 */
class PayPalService
{
    private static function isLive(): bool
    {
        return strtolower($_ENV['PAYPAL_MODE'] ?? 'sandbox') === 'live';
    }

    private static function apiBase(): string
    {
        return self::isLive()
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public static function clientId(): string
    {
        return $_ENV['PAYPAL_CLIENT_ID'] ?? '';
    }

    public static function currency(): string
    {
        return $_ENV['PAYPAL_CURRENCY'] ?? 'CAD';
    }

    private static function clientSecret(): string
    {
        return $_ENV['PAYPAL_CLIENT_SECRET'] ?? '';
    }

    /**
     * Low-level cURL helper shared by every call to the PayPal API.
     */
    private static function request(string $method, string $url, array $headers, ?array $body = null): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            // Unlike the legacy essahockey integration, TLS verification is
            // left ON -- there's no legitimate reason to disable it here.
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT        => 20,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("PayPal request failed: {$error}");
        }

        $decoded = json_decode($response, true) ?? [];

        if ($status >= 400) {
            $message = $decoded['message'] ?? ($decoded['error_description'] ?? "PayPal API error (HTTP {$status})");
            throw new \RuntimeException($message);
        }

        return $decoded;
    }

    /**
     * OAuth2 client-credentials grant -- required before every Orders API call.
     */
    public static function getAccessToken(): string
    {
        $clientId = self::clientId();
        $secret = self::clientSecret();

        if ($clientId === '' || $secret === '') {
            throw new \RuntimeException('PayPal is not configured yet. Set PAYPAL_CLIENT_ID / PAYPAL_CLIENT_SECRET in .env.');
        }

        $ch = curl_init(self::apiBase() . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => "{$clientId}:{$secret}",
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode((string)$response, true) ?? [];

        if ($status >= 400 || empty($decoded['access_token'])) {
            throw new \RuntimeException('Failed to authenticate with PayPal: ' . ($decoded['error_description'] ?? 'unknown error'));
        }

        return $decoded['access_token'];
    }

    /**
     * Create a PayPal Order for one registration. $referenceId is the
     * registration's encoded id -- PayPal echoes it back on capture/webhook
     * so the payment can be matched back to the right registrant, the same
     * role the legacy site's `purchase_units[0].reference_id` field plays.
     */
    public static function createOrder(string $referenceId, float $amount, string $description): array
    {
        $accessToken = self::getAccessToken();

        return self::request('POST', self::apiBase() . '/v2/checkout/orders', [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ], [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $referenceId,
                'description' => $description,
                'amount' => [
                    'currency_code' => self::currency(),
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        'brand_name' => $_ENV['APP_NAME'] ?? 'Canadian All Star Sports',
                        'locale' => 'en-CA',
                        'landing_page' => 'LOGIN',
                        'user_action' => 'PAY_NOW',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Capture a previously-approved order (called by the browser right
     * after the PayPal popup approves it -- fast path for the UI). The
     * webhook handler performs the same capture-confirmation logic
     * server-side as a reliability backstop in case this call never lands
     * (closed tab, network drop, etc).
     */
    public static function captureOrder(string $orderId): array
    {
        $accessToken = self::getAccessToken();

        return self::request('POST', self::apiBase() . "/v2/checkout/orders/{$orderId}/capture", [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ], (object)[]);
    }

    public static function getOrder(string $orderId): array
    {
        $accessToken = self::getAccessToken();

        return self::request('GET', self::apiBase() . "/v2/checkout/orders/{$orderId}", [
            "Authorization: Bearer {$accessToken}",
        ]);
    }

    /**
     * Verify an incoming webhook's signature via PayPal's own verification
     * endpoint. This is the piece the legacy site never had -- without it,
     * anyone who discovers the webhook URL could POST a fake
     * "PAYMENT.CAPTURE.COMPLETED" event and mark a registration paid for
     * free.
     *
     * @param array $headers Raw request headers (case-insensitive keys expected).
     * @param string $rawBody The raw request body exactly as received.
     */
    public static function verifyWebhookSignature(array $headers, string $rawBody): bool
    {
        $webhookId = $_ENV['PAYPAL_WEBHOOK_ID'] ?? '';
        if ($webhookId === '') {
            // Not configured yet -- fail closed. Once a webhook ID from the
            // PayPal Developer Dashboard is set in .env, this starts
            // enforcing real signature checks.
            return false;
        }

        $find = function (array $headers, string $name): ?string {
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, $name) === 0) return $value;
            }
            return null;
        };

        $transmissionId = $find($headers, 'Paypal-Transmission-Id');
        $transmissionTime = $find($headers, 'Paypal-Transmission-Time');
        $certUrl = $find($headers, 'Paypal-Cert-Url');
        $authAlgo = $find($headers, 'Paypal-Auth-Algo');
        $transmissionSig = $find($headers, 'Paypal-Transmission-Sig');

        if (!$transmissionId || !$transmissionTime || !$certUrl || !$authAlgo || !$transmissionSig) {
            return false;
        }

        $accessToken = self::getAccessToken();

        $result = self::request('POST', self::apiBase() . '/v1/notifications/verify-webhook-signature', [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ], [
            'transmission_id' => $transmissionId,
            'transmission_time' => $transmissionTime,
            'cert_url' => $certUrl,
            'auth_algo' => $authAlgo,
            'transmission_sig' => $transmissionSig,
            'webhook_id' => $webhookId,
            'webhook_event' => json_decode($rawBody, true),
        ]);

        return ($result['verification_status'] ?? '') === 'SUCCESS';
    }
}
