-- Membership plans and digital catalog expansion.
-- Safe to re-run: uses CREATE TABLE IF NOT EXISTS. Column additions to the
-- existing products table are applied idempotently by MigrationService.

CREATE TABLE IF NOT EXISTS membership_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    tagline VARCHAR(255) NULL,
    description MEDIUMTEXT NULL,
    price_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    billing_interval ENUM('one_time','weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
    trial_days INT UNSIGNED NOT NULL DEFAULT 0,
    stripe_price_id VARCHAR(190) NULL,
    stripe_payment_link VARCHAR(500) NULL,
    paypal_plan_id VARCHAR(190) NULL,
    paypal_subscribe_url VARCHAR(500) NULL,
    features JSON NULL,
    badge VARCHAR(60) NULL,
    cta_label VARCHAR(80) NOT NULL DEFAULT 'Get started',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX membership_plans_active_order (is_active, sort_order),
    INDEX membership_plans_featured (is_featured, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional membership signups captured before/after a gateway subscription is
-- confirmed. Ties a customer email to a plan so the backend can report demand.
CREATE TABLE IF NOT EXISTS membership_signups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(190) NOT NULL,
    name VARCHAR(190) NULL,
    provider ENUM('stripe','paypal','manual','pending') NOT NULL DEFAULT 'pending',
    provider_reference VARCHAR(255) NULL,
    status ENUM('pending','active','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX membership_signups_plan (plan_id, status),
    INDEX membership_signups_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
