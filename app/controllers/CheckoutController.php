<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\CodeShopRepository;
use App\Models\PayPalSettingsRepository;
use App\Services\AuditLogger;

final class CheckoutController extends Controller
{
    public function start(string $slug): void
    {
        Security::ensureSession();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->render('errors/404', ['title' => 'Checkout Session Expired']);
            return;
        }

        $gateway = (string) ($_POST['gateway'] ?? 'stripe');
        $product = (new CodeShopRepository())->findBySlug($slug);
        if ($product === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Product Not Found']);
            return;
        }

        if ($gateway === 'paypal') {
            $this->redirectToPayPal($product);
            return;
        }

        $this->redirectToStripe($product);
    }

    private function redirectToPayPal(array $product): never
    {
        $settings = (new PayPalSettingsRepository())->current();
        $url = (string) ($product['paypal_checkout_url'] ?: $settings['shop_paypal_checkout_url'] ?: $settings['credits_checkout_url'] ?: '');

        if ($url === '') {
            (new AuditLogger())->log('shop.paypal_checkout.missing_url', ['product' => $product['slug'] ?? '']);
            $url = '/contact?product=' . rawurlencode((string) ($product['slug'] ?? 'code-shop'));
        }

        $this->redirect($url);
    }

    private function redirectToStripe(array $product): void
    {
        $settings = (new PayPalSettingsRepository())->current();
        $shopConfig = require base_path('config/shop.php');
        $secretKey = (string) ($settings['stripe_secret_key'] ?: $shopConfig['stripe_secret_key'] ?: '');
        $priceId = $this->stripePriceId($product, $settings);

        if ($secretKey === '' || $priceId === '') {
            (new AuditLogger())->log('shop.stripe_checkout.missing_config', [
                'product' => $product['slug'] ?? '',
                'has_secret' => $secretKey !== '',
                'price_id' => $priceId,
            ]);
            $this->redirect('/contact?product=' . rawurlencode((string) ($product['slug'] ?? 'code-shop')));
        }

        $successUrl = (string) ($settings['stripe_success_url'] ?: rtrim((string) $this->config['url'], '/') . '/code-shop/' . $product['slug'] . '?payment=success');
        $cancelUrl = (string) ($settings['stripe_cancel_url'] ?: rtrim((string) $this->config['url'], '/') . '/code-shop/' . $product['slug'] . '?payment=cancelled');

        $body = http_build_query([
            'mode' => 'payment',
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => 1,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata[product_id]' => (string) $product['id'],
            'metadata[product_slug]' => (string) $product['slug'],
            'allow_promotion_codes' => 'true',
            'billing_address_collection' => 'auto',
            'shipping_address_collection[allowed_countries][0]' => 'IE',
            'shipping_address_collection[allowed_countries][1]' => 'GB',
            'shipping_address_collection[allowed_countries][2]' => 'US',
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Authorization: Bearer ' . $secretKey,
                    'Content-Type: application/x-www-form-urlencoded',
                ]),
                'content' => $body,
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);

        $response = file_get_contents('https://api.stripe.com/v1/checkout/sessions', false, $context);
        $data = is_string($response) ? json_decode($response, true) : null;

        if (!is_array($data) || empty($data['url'])) {
            (new AuditLogger())->log('shop.stripe_checkout.failed', [
                'product' => $product['slug'] ?? '',
                'response' => is_array($data) ? ($data['error']['message'] ?? 'unknown') : 'empty response',
            ]);
            $this->redirect('/contact?product=' . rawurlencode((string) ($product['slug'] ?? 'code-shop')));
        }

        $this->redirect((string) $data['url']);
    }

    private function stripePriceId(array $product, array $settings): string
    {
        if (!empty($product['stripe_price_id'])) {
            return (string) $product['stripe_price_id'];
        }

        if (($product['slug'] ?? '') === 'photo-to-key-php-website-backend') {
            return (string) ($settings['stripe_price_photo_to_key'] ?? '');
        }

        return '';
    }
}
