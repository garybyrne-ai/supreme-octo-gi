<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Security;
use App\Models\CommerceRepository;

final class DigitalCommerceEngine
{
    private const MAX_PACKAGE_BYTES = 268435456;

    public function __construct(private readonly CommerceRepository $repository = new CommerceRepository())
    {
    }

    public function saveProduct(array $input): int
    {
        $slug = $this->slug((string) ($input['slug'] ?? $input['title'] ?? 'product'));
        if ($slug === '') {
            throw new \RuntimeException('Product slug is required.');
        }

        $price = (int) round(((float) ($input['price'] ?? 0)) * 100);
        $productType = (string) ($input['product_type'] ?? 'file');
        $productType = in_array($productType, ['file', 'snippet'], true) ? $productType : 'file';

        return $this->repository->upsertProduct([
            'slug' => $slug,
            'title' => trim((string) ($input['title'] ?? 'Untitled Digital Product')),
            'summary' => trim((string) ($input['summary'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'product_type' => $productType,
            'platform' => trim((string) ($input['platform'] ?? 'Core PHP')),
            'platform_tags' => $this->tags((string) ($input['platform_tags'] ?? '')),
            'price_cents' => max(0, $price),
            'currency' => strtoupper(substr((string) ($input['currency'] ?? 'USD'), 0, 3)),
            'private_file_path' => $input['private_file_path'] ?? null,
            'snippet_html' => $input['snippet_html'] ?? null,
            'snippet_css' => $input['snippet_css'] ?? null,
            'snippet_js' => $input['snippet_js'] ?? null,
            'checkout_url' => $input['checkout_url'] ?? null,
            'paypal_checkout_url' => $input['paypal_checkout_url'] ?? null,
            'stripe_price_id' => $input['stripe_price_id'] ?? null,
            'is_active' => !empty($input['is_active']),
            'sort_order' => (int) ($input['sort_order'] ?? 100),
        ]);
    }

    public function uploadPackage(array $file, int $productId, string $version = '1.0.0'): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Package upload failed.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > self::MAX_PACKAGE_BYTES) {
            throw new \RuntimeException('Digital packages must be smaller than 256MB.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp)) {
            throw new \RuntimeException('Uploaded package could not be verified.');
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            throw new \RuntimeException('Only ZIP packages can be attached to digital products.');
        }

        $shopConfig = require base_path('config/shop.php');
        $privateRoot = rtrim((string) $shopConfig['private_dir'], DIRECTORY_SEPARATOR);
        if (!is_dir($privateRoot)) {
            mkdir($privateRoot, 0750, true);
        }

        $safeName = 'product-' . $productId . '-' . $this->slug($version) . '-' . bin2hex(random_bytes(4)) . '.zip';
        $target = $privateRoot . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($tmp, $target)) {
            throw new \RuntimeException('Could not move ZIP package into private storage.');
        }
        chmod($target, 0640);

        $record = [
            'product_id' => $productId,
            'asset_type' => 'download',
            'storage_driver' => 'private',
            'storage_path' => $safeName,
            'original_name' => (string) ($file['name'] ?? $safeName),
            'mime_type' => 'application/zip',
            'size_bytes' => filesize($target) ?: $size,
            'checksum_sha256' => hash_file('sha256', $target),
            'version_label' => $version !== '' ? $version : '1.0.0',
            'is_active' => true,
        ];
        $record['id'] = $this->repository->saveProductAsset($record);

        return $record;
    }

    public function openOrder(array $customer, array $items, string $gatewayProvider = 'stripe'): array
    {
        return $this->repository->createOrder([
            'customer' => $customer,
            'status' => 'pending_payment',
            'gateway_provider' => in_array($gatewayProvider, ['stripe', 'paypal', 'manual'], true) ? $gatewayProvider : 'stripe',
            'currency' => $items[0]['currency'] ?? 'USD',
            'ip_address' => Security::clientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ], $items);
    }

    public function issueDownloadGrant(int $orderItemId, int $productAssetId, int $ttlHours = 24, int $maxDownloads = 3): array
    {
        return $this->repository->createDownloadGrant($orderItemId, $productAssetId, $ttlHours, $maxDownloads);
    }

    private function tags(string $value): array
    {
        $tags = array_map('trim', explode(',', $value));
        return array_values(array_filter($tags, static fn (string $tag): bool => $tag !== ''));
    }

    private function slug(string $value): string
    {
        $value = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '');
        return trim($value, '-');
    }
}
