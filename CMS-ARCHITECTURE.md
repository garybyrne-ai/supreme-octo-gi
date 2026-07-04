# Crest Web Media Enterprise CMS Architecture

This backend is structured as a lightweight Core PHP 8 CMS with strict MVC boundaries, PDO prepared statements and deployability on Cloudways, DigitalOcean, or a dedicated VPS.

## Runtime Layers

- Public MVC: `public/index.php`, `routes/web.php`, `app/controllers`, `app/views`.
- CMS controllers: admin-only endpoints for theme assets, typography rules, injections and commerce operations.
- Repositories: table-focused PDO classes that own persistence and transaction boundaries.
- Services: validation, compilation and secure file/payment workflows.
- Storage:
  - Public assets: `public/uploads/theme` and `public/uploads/media`.
  - Private downloads: configured in `config/shop.php`, defaulting above the web root.
  - Cloud-ready assets: `product_assets.storage_driver` supports private disk, S3, Cloudflare R2 and DigitalOcean Spaces metadata.

## Module Blueprint

### 1. Global Theme and Asset Console

- `theme_assets`: stores logos, dark/light marks, favicons and social cards with MIME, checksum and variant tracking.
- `typography_rules`: maps verified Google/system font choices to explicit selectors such as `h1`, `body`, `buttons`, `main-menu` and `mega-dropdowns`.
- `theme_style_cache`: stores the compiled CSS variable map so pages render without database-heavy style generation or layout shifts.
- `CmsThemeAssetManager`: validates SVG/WebP/PNG/ICO uploads, blocks SVG scripts and writes normalized asset records.
- `TypographyEngine`: exposes a verified Google Font array and compiles selector rules into a cached style block.

### 2. Digital Product Commerce Engine

- `products`: existing catalog table remains the canonical product record.
- `product_assets`: links downloadable ZIPs, snippet bundles or license bundles to products.
- `commerce_customers`, `commerce_orders`, `commerce_order_items`: WooCommerce-style order lifecycle.
- `commerce_transactions`: Stripe/PayPal/manual payment records.
- `commerce_invoices`: invoice metadata and generated PDF path targets.
- `product_license_policies`, `license_keys`, `license_activations`: EDD-style software licensing.
- `download_grants`, `download_events`: short-lived, count-limited secure download access.
- `gateway_settings`, `commerce_webhook_events`, `commerce_subscriptions`: gateway configuration, idempotent webhook processing and Growth Lab Pass subscriptions.

### 3. Script and Code Wrapper Inventory

- `custom_code_assets`: minified custom CSS/JS inventory with checksums and load strategy.
- `script_injections`: controlled injection points for `head`, `body_open` and `footer_close`.
- `ScriptInjectionGuard`: blocks PHP execution, JavaScript URLs, event-handler payloads and unapproved remote script hosts while allowing legitimate analytics/payment scripts.

## Deployment Notes

1. Run `database/schema.sql` on a clean install.
2. Run migrations in order from `database/migrations`, skipping `001_create_core_tables.sql` if the schema was already loaded manually.
3. Keep private code packages outside `public_html`; set `CODE_SHOP_PRIVATE_DIR` if Cloudways uses a custom private path.
4. Configure Stripe and PayPal in admin/payment settings, then register webhooks:
   - Stripe: `/webhooks/stripe`
   - PayPal: add a future `/webhooks/paypal` route when PayPal REST webhook validation is enabled.
5. Varnish should bypass `/admin`, `/account`, `/download.php`, `/webhooks`, checkout and support routes.
