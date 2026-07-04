CREATE TABLE IF NOT EXISTS theme_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_key VARCHAR(80) NOT NULL,
    variant ENUM('primary','dark','light','icon','favicon','social') NOT NULL DEFAULT 'primary',
    disk ENUM('public','private') NOT NULL DEFAULT 'public',
    path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    checksum_sha256 CHAR(64) NOT NULL,
    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX theme_assets_active_key (asset_key, variant, is_active),
    INDEX theme_assets_key_variant (asset_key, variant),
    INDEX theme_assets_checksum (checksum_sha256),
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS typography_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    selector_key VARCHAR(80) NOT NULL,
    css_selector VARCHAR(255) NOT NULL,
    font_family VARCHAR(120) NOT NULL,
    font_source ENUM('local','google','system') NOT NULL DEFAULT 'google',
    font_weights JSON NOT NULL,
    fallback_stack VARCHAR(255) NOT NULL,
    font_display ENUM('swap','optional','fallback') NOT NULL DEFAULT 'swap',
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY typography_selector_key (selector_key),
    INDEX typography_enabled_order (is_enabled, sort_order),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS theme_style_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(120) NOT NULL UNIQUE,
    css MEDIUMTEXT NOT NULL,
    checksum_sha256 CHAR(64) NOT NULL,
    built_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX theme_style_cache_built (built_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS custom_code_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_type ENUM('css','js') NOT NULL,
    name VARCHAR(160) NOT NULL,
    code MEDIUMTEXT NOT NULL,
    compiled_path VARCHAR(500) NULL,
    load_strategy ENUM('inline','defer','async','module') NOT NULL DEFAULT 'inline',
    checksum_sha256 CHAR(64) NOT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX custom_code_type_enabled (asset_type, is_enabled),
    INDEX custom_code_checksum (checksum_sha256),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS script_injections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    location ENUM('head','body_open','footer_close') NOT NULL,
    code MEDIUMTEXT NOT NULL,
    code_hash CHAR(64) NOT NULL,
    load_strategy ENUM('inline','defer','async','module') NOT NULL DEFAULT 'inline',
    is_enabled TINYINT(1) NOT NULL DEFAULT 0,
    approved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX script_location_enabled (location, is_enabled),
    INDEX script_code_hash (code_hash),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gateway_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('stripe','paypal') NOT NULL,
    mode ENUM('sandbox','live') NOT NULL DEFAULT 'sandbox',
    public_key VARCHAR(255) NULL,
    secret_key TEXT NULL,
    webhook_secret TEXT NULL,
    webhook_id VARCHAR(255) NULL,
    metadata JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY gateway_provider_mode (provider, mode),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    name VARCHAR(190) NULL,
    company VARCHAR(190) NULL,
    vat_number VARCHAR(80) NULL,
    billing_address JSON NULL,
    shipping_address JSON NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX commerce_customers_company (company),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    asset_type ENUM('download','snippet','license_bundle') NOT NULL DEFAULT 'download',
    storage_driver ENUM('private','s3','cloudflare_r2','digitalocean_spaces') NOT NULL DEFAULT 'private',
    storage_path VARCHAR(700) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NOT NULL DEFAULT 'application/zip',
    size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    checksum_sha256 CHAR(64) NOT NULL,
    version_label VARCHAR(80) NOT NULL DEFAULT '1.0.0',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX product_assets_product_active (product_id, is_active),
    INDEX product_assets_checksum (checksum_sha256),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_license_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    activation_limit INT UNSIGNED NOT NULL DEFAULT 1,
    expires_after_days INT UNSIGNED NULL,
    support_days INT UNSIGNED NULL,
    update_channel ENUM('stable','beta','lts') NOT NULL DEFAULT 'stable',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX license_policy_product_active (product_id, is_active),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_reference VARCHAR(48) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft','pending_payment','paid','processing','fulfilled','failed','refunded','cancelled') NOT NULL DEFAULT 'draft',
    subtotal_cents INT UNSIGNED NOT NULL DEFAULT 0,
    tax_cents INT UNSIGNED NOT NULL DEFAULT 0,
    total_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    gateway_provider ENUM('stripe','paypal','manual') NOT NULL DEFAULT 'stripe',
    gateway_session_id VARCHAR(255) NULL,
    gateway_payment_id VARCHAR(255) NULL,
    ip_address VARCHAR(80) NULL,
    user_agent VARCHAR(500) NULL,
    metadata JSON NULL,
    paid_at DATETIME NULL,
    fulfilled_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX commerce_orders_customer_created (customer_id, created_at),
    INDEX commerce_orders_status_created (status, created_at),
    INDEX commerce_orders_gateway_session (gateway_provider, gateway_session_id),
    FOREIGN KEY (customer_id) REFERENCES commerce_customers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_asset_id BIGINT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price_cents INT UNSIGNED NOT NULL DEFAULT 0,
    tax_cents INT UNSIGNED NOT NULL DEFAULT 0,
    total_cents INT UNSIGNED NOT NULL DEFAULT 0,
    fulfillment_status ENUM('pending','granted','downloaded','revoked') NOT NULL DEFAULT 'pending',
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX order_items_order (order_id),
    INDEX order_items_product (product_id),
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (product_asset_id) REFERENCES product_assets(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    provider ENUM('stripe','paypal','manual') NOT NULL,
    provider_event_id VARCHAR(255) NULL,
    provider_payment_id VARCHAR(255) NULL,
    status ENUM('requires_action','authorized','captured','failed','refunded','disputed') NOT NULL,
    amount_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    raw_payload JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY commerce_transaction_event (provider, provider_event_id),
    INDEX commerce_transactions_order (order_id),
    INDEX commerce_transactions_payment (provider, provider_payment_id),
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(60) NOT NULL UNIQUE,
    invoice_path VARCHAR(500) NULL,
    billing_snapshot JSON NOT NULL,
    issued_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX commerce_invoices_order (order_id),
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS license_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    policy_id BIGINT UNSIGNED NULL,
    license_hash CHAR(64) NOT NULL UNIQUE,
    status ENUM('active','suspended','expired','revoked') NOT NULL DEFAULT 'active',
    activation_limit INT UNSIGNED NOT NULL DEFAULT 1,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX license_keys_product_status (product_id, status),
    INDEX license_keys_order_item (order_item_id),
    FOREIGN KEY (order_item_id) REFERENCES commerce_order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (policy_id) REFERENCES product_license_policies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS license_activations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    license_key_id BIGINT UNSIGNED NOT NULL,
    site_url VARCHAR(255) NOT NULL,
    instance_hash CHAR(64) NOT NULL,
    ip_address VARCHAR(80) NULL,
    user_agent VARCHAR(500) NULL,
    activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP NULL,
    revoked_at TIMESTAMP NULL,
    UNIQUE KEY license_instance (license_key_id, instance_hash),
    INDEX license_activation_site (site_url),
    FOREIGN KEY (license_key_id) REFERENCES license_keys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS download_grants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED NOT NULL,
    product_asset_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    max_downloads INT UNSIGNED NOT NULL DEFAULT 3,
    download_count INT UNSIGNED NOT NULL DEFAULT 0,
    revoked_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX download_grants_item (order_item_id),
    INDEX download_grants_token_expiry (token_hash, expires_at),
    FOREIGN KEY (order_item_id) REFERENCES commerce_order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (product_asset_id) REFERENCES product_assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS download_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    download_grant_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(80) NULL,
    user_agent VARCHAR(500) NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX download_events_grant_created (download_grant_id, downloaded_at),
    FOREIGN KEY (download_grant_id) REFERENCES download_grants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    provider ENUM('stripe','paypal') NOT NULL,
    provider_subscription_id VARCHAR(255) NOT NULL,
    plan_code VARCHAR(80) NOT NULL,
    status ENUM('trialing','active','past_due','paused','cancelled','expired') NOT NULL DEFAULT 'active',
    current_period_ends_at DATETIME NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY commerce_subscription_provider (provider, provider_subscription_id),
    INDEX commerce_subscriptions_customer_status (customer_id, status),
    FOREIGN KEY (customer_id) REFERENCES commerce_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commerce_webhook_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('stripe','paypal') NOT NULL,
    event_id VARCHAR(255) NOT NULL,
    event_type VARCHAR(190) NOT NULL,
    status ENUM('received','processed','ignored','failed') NOT NULL DEFAULT 'received',
    raw_payload JSON NOT NULL,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    UNIQUE KEY commerce_webhook_provider_event (provider, event_id),
    INDEX commerce_webhook_type_status (event_type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
