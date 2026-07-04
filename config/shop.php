<?php

declare(strict_types=1);

$root = defined('BASE_PATH') ? (string) constant('BASE_PATH') : dirname(__DIR__);

return [
    'private_dir' => getenv('CODE_SHOP_PRIVATE_DIR') ?: dirname($root) . '/private/code-shop',
    'stripe_secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
    'stripe_webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
    'download_ttl_hours' => 24,
    'download_max_count' => 3,
    'cloud_storage' => [
        'driver' => getenv('CODE_SHOP_STORAGE_DRIVER') ?: 'private',
        's3_bucket' => getenv('CODE_SHOP_S3_BUCKET') ?: '',
        's3_region' => getenv('CODE_SHOP_S3_REGION') ?: '',
        's3_endpoint' => getenv('CODE_SHOP_S3_ENDPOINT') ?: '',
        'access_key' => getenv('CODE_SHOP_STORAGE_ACCESS_KEY') ?: '',
        'secret_key' => getenv('CODE_SHOP_STORAGE_SECRET_KEY') ?: '',
    ],
];
