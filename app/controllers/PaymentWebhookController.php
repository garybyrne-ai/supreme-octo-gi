<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CodeShopRepository;
use App\Models\PayPalSettingsRepository;
use App\Services\AuditLogger;
use App\Services\LeadMailer;

final class PaymentWebhookController extends Controller
{
    public function stripe(): void
    {
        $payload = (string) file_get_contents('php://input');
        $signature = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
        $shopConfig = require base_path('config/shop.php');
        $paymentSettings = (new PayPalSettingsRepository())->current();
        $secret = (string) ($paymentSettings['stripe_webhook_secret'] ?: $shopConfig['stripe_webhook_secret'] ?: '');

        if ($secret === '') {
            $this->json(['error' => 'Stripe webhook secret is not configured.'], 500);
            return;
        }

        if (!$this->validStripeSignature($payload, $signature, $secret)) {
            (new AuditLogger())->log('shop.stripe_webhook.invalid_signature');
            $this->json(['error' => 'Invalid signature.'], 400);
            return;
        }

        $event = json_decode($payload, true);
        if (!is_array($event)) {
            $this->json(['error' => 'Invalid JSON payload.'], 400);
            return;
        }

        if (($event['type'] ?? '') !== 'checkout.session.completed') {
            $this->recordCommerceWebhook('stripe', (string) ($event['id'] ?? hash('sha256', $payload)), (string) ($event['type'] ?? 'unknown'), $event, 'ignored');
            $this->json(['received' => true, 'ignored' => true]);
            return;
        }

        $this->recordCommerceWebhook('stripe', (string) ($event['id'] ?? hash('sha256', $payload)), (string) ($event['type'] ?? 'checkout.session.completed'), $event, 'received');
        $session = $event['data']['object'] ?? null;
        if (!is_array($session)) {
            $this->json(['error' => 'Missing checkout session.'], 422);
            return;
        }

        try {
            $shop = new CodeShopRepository();
            $product = $this->productFromSession($shop, $session);
            if ($product === null) {
                $this->json(['error' => 'Product metadata was not found.'], 422);
                return;
            }

            $purchase = $shop->recordStripePurchase($session, $event, $product);
            $downloadUrl = null;

            if (($product['product_type'] ?? '') === 'file') {
                $link = $shop->createSecureLink(
                    (int) $purchase['id'],
                    (int) $product['id'],
                    (int) ($shopConfig['download_ttl_hours'] ?? 24)
                );

                if (!empty($link['token'])) {
                    $downloadUrl = rtrim((string) ($this->config['url'] ?? ''), '/') . '/download.php?token=' . rawurlencode((string) $link['token']);
                }
            }

            if (!empty($purchase['customer_email'])) {
                (new LeadMailer())->sendDigitalProductDelivery(
                    (string) $purchase['customer_email'],
                    (string) ($purchase['customer_name'] ?? ''),
                    $product,
                    $downloadUrl
                );
            }

            (new AuditLogger())->log('shop.stripe_webhook.purchase_recorded', [
                'product' => $product['slug'] ?? '',
                'session' => $session['id'] ?? '',
                'download_created' => $downloadUrl !== null,
            ]);

            $this->markCommerceWebhookProcessed('stripe', (string) ($event['id'] ?? hash('sha256', $payload)), 'processed');
            $this->json(['received' => true, 'purchase_id' => (int) $purchase['id']]);
        } catch (\Throwable $exception) {
            (new AuditLogger())->log('shop.stripe_webhook.failed', ['error' => $exception->getMessage()]);
            $this->markCommerceWebhookProcessed('stripe', (string) ($event['id'] ?? hash('sha256', $payload)), 'failed');
            $this->json(['error' => 'Webhook processing failed.'], 500);
        }
    }

    public function paypal(): void
    {
        $payload = (string) file_get_contents('php://input');
        $event = json_decode($payload, true);
        if (!is_array($event)) {
            $this->json(['error' => 'Invalid JSON payload.'], 400);
            return;
        }

        $eventId = (string) ($event['id'] ?? ($_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? hash('sha256', $payload)));
        $eventType = (string) ($event['event_type'] ?? 'paypal.event');
        $hasTransmissionHeaders = !empty($_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'])
            && !empty($_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'])
            && !empty($_SERVER['HTTP_PAYPAL_CERT_URL']);

        $this->recordCommerceWebhook('paypal', $eventId, $eventType, $event, 'received');

        if (!$hasTransmissionHeaders) {
            (new AuditLogger())->log('shop.paypal_webhook.missing_signature_headers', ['event_id' => $eventId]);
            $this->markCommerceWebhookProcessed('paypal', $eventId, 'failed');
            $this->json(['error' => 'Missing PayPal webhook signature headers.'], 400);
            return;
        }

        (new AuditLogger())->log('shop.paypal_webhook.received', [
            'event_id' => $eventId,
            'event_type' => $eventType,
        ]);

        $this->markCommerceWebhookProcessed('paypal', $eventId, 'processed');
        $this->json(['received' => true, 'event_id' => $eventId]);
    }

    private function productFromSession(CodeShopRepository $shop, array $session): ?array
    {
        $metadata = is_array($session['metadata'] ?? null) ? $session['metadata'] : [];

        if (!empty($metadata['product_id'])) {
            return $shop->findById((int) $metadata['product_id']);
        }

        if (!empty($metadata['product_slug'])) {
            return $shop->findBySlug((string) $metadata['product_slug']);
        }

        return null;
    }

    private function validStripeSignature(string $payload, string $signature, string $secret): bool
    {
        if ($payload === '' || $signature === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($key !== '' && $value !== '') {
                $parts[$key][] = $value;
            }
        }

        $timestamp = (int) ($parts['t'][0] ?? 0);
        if ($timestamp <= 0 || abs(time() - $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($parts['v1'] ?? [] as $provided) {
            if (hash_equals($expected, $provided)) {
                return true;
            }
        }

        return false;
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    private function recordCommerceWebhook(string $provider, string $eventId, string $eventType, array $payload, string $status): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO commerce_webhook_events (provider, event_id, event_type, status, raw_payload)
                 VALUES (:provider, :event_id, :event_type, :status, :raw_payload)
                 ON DUPLICATE KEY UPDATE status = VALUES(status), raw_payload = VALUES(raw_payload)'
            );
            $stmt->execute([
                'provider' => $provider,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'status' => $status,
                'raw_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            ]);
        } catch (\Throwable) {
        }
    }

    private function markCommerceWebhookProcessed(string $provider, string $eventId, string $status): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'UPDATE commerce_webhook_events
                 SET status = :status, processed_at = UTC_TIMESTAMP()
                 WHERE provider = :provider AND event_id = :event_id'
            );
            $stmt->execute([
                'provider' => $provider,
                'event_id' => $eventId,
                'status' => $status,
            ]);
        } catch (\Throwable) {
        }
    }
}
