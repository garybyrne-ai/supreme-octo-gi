<?php

declare(strict_types=1);

namespace App\Models;

final class PayPalSettingsRepository
{
    public function current(): array
    {
        $default = [
            'mode' => 'sandbox',
            'merchant_id' => '',
            'client_id' => '',
            'client_secret' => '',
            'webhook_id' => '',
            'currency' => 'USD',
            'credits_checkout_url' => '',
            'growth_lab_19_url' => '',
            'growth_lab_49_url' => '',
            'shop_paypal_checkout_url' => '',
            'stripe_secret_key' => '',
            'stripe_webhook_secret' => '',
            'stripe_price_photo_to_key' => '',
            'stripe_success_url' => '',
            'stripe_cancel_url' => '',
            'serp_api_key' => '',
        ];

        $path = $this->path();
        if (!is_file($path)) {
            return $default;
        }

        $saved = json_decode((string) file_get_contents($path), true);
        return is_array($saved) ? array_replace($default, $saved) : $default;
    }

    public function save(array $input): array
    {
        $current = $this->current();
        $settings = [
            'mode' => trim((string) ($input['mode'] ?? 'sandbox')),
            'merchant_id' => trim((string) ($input['merchant_id'] ?? '')),
            'client_id' => trim((string) ($input['client_id'] ?? '')),
            'client_secret' => (string) ($input['client_secret'] ?? ''),
            'webhook_id' => trim((string) ($input['webhook_id'] ?? '')),
            'currency' => strtoupper(trim((string) ($input['currency'] ?? 'USD'))),
            'credits_checkout_url' => trim((string) ($input['credits_checkout_url'] ?? '')),
            'growth_lab_19_url' => trim((string) ($input['growth_lab_19_url'] ?? '')),
            'growth_lab_49_url' => trim((string) ($input['growth_lab_49_url'] ?? '')),
            'shop_paypal_checkout_url' => trim((string) ($input['shop_paypal_checkout_url'] ?? '')),
            'stripe_secret_key' => (string) ($input['stripe_secret_key'] ?? ''),
            'stripe_webhook_secret' => (string) ($input['stripe_webhook_secret'] ?? ''),
            'stripe_price_photo_to_key' => trim((string) ($input['stripe_price_photo_to_key'] ?? '')),
            'stripe_success_url' => trim((string) ($input['stripe_success_url'] ?? '')),
            'stripe_cancel_url' => trim((string) ($input['stripe_cancel_url'] ?? '')),
            'serp_api_key' => trim((string) ($input['serp_api_key'] ?? '')),
        ];

        if ($settings['serp_api_key'] === '') {
            $settings['serp_api_key'] = (string) ($current['serp_api_key'] ?? '');
        }

        if (!in_array($settings['mode'], ['sandbox', 'live'], true)) {
            throw new \RuntimeException('Choose PayPal sandbox or live mode.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $settings['currency'])) {
            throw new \RuntimeException('Currency must be a 3-letter code such as USD, EUR or GBP.');
        }

        if ($settings['client_secret'] === '') {
            $settings['client_secret'] = (string) ($current['client_secret'] ?? '');
        }

        if ($settings['stripe_secret_key'] === '') {
            $settings['stripe_secret_key'] = (string) ($current['stripe_secret_key'] ?? '');
        }

        if ($settings['stripe_webhook_secret'] === '') {
            $settings['stripe_webhook_secret'] = (string) ($current['stripe_webhook_secret'] ?? '');
        }

        foreach (['credits_checkout_url', 'growth_lab_19_url', 'growth_lab_49_url', 'shop_paypal_checkout_url', 'stripe_success_url', 'stripe_cancel_url'] as $urlKey) {
            if ($settings[$urlKey] !== '' && !filter_var($settings[$urlKey], FILTER_VALIDATE_URL)) {
                throw new \RuntimeException('PayPal checkout and subscription links must be valid URLs.');
            }
        }

        if ($settings['stripe_price_photo_to_key'] !== '' && !preg_match('/^price_[A-Za-z0-9_]+$/', $settings['stripe_price_photo_to_key'])) {
            throw new \RuntimeException('Stripe price id should look like price_123.');
        }

        $dir = dirname($this->path());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path(), json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $settings;
    }

    private function path(): string
    {
        return base_path('storage/data/paypal-settings.json');
    }
}
