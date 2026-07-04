USE crest_web_media;

INSERT INTO roles (name) VALUES ('Admin'), ('Editor'), ('Author');

INSERT INTO users (name, email, password_hash)
VALUES ('Crest Admin', 'admin@crestwebmedia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');

INSERT INTO categories (name, slug, type) VALUES
('PHP', 'php', 'post'),
('SEO', 'seo', 'post'),
('Security', 'security', 'post'),
('Performance', 'performance', 'post'),
('AI Development', 'ai-development', 'post');

INSERT INTO services (title, slug, summary, body, icon, sort_order, is_featured) VALUES
('Website Development', 'website-development', 'Lean PHP 8/Laravel platforms built for sub-second edge paint, secure CMS control and qualified B2B pipeline.', 'Custom websites and CMS platforms built around performance, security, search visibility and revenue.', 'fa-code', 1, 1),
('Web Design', 'web-design', 'Conversion-mapped UI systems engineered to reduce cognitive friction and make complex offers easier to buy.', 'Interface design for websites, apps and digital products that need trust, clarity and measurable action.', 'fa-wand-magic-sparkles', 2, 1),
('App Development', 'app-development', 'Workflow-led web and mobile products with role-aware dashboards, API foundations and resilient data operations.', 'Mobile and web app delivery with data structure, user roles, dashboards and release support.', 'fa-mobile-screen-button', 3, 1),
('API Integration', 'api-integration', 'Decoupled API and webhook layers for payments, CRMs, booking engines and automation stacks.', 'Secure API integration work for business systems, payments, webhooks, CRMs and automations.', 'fa-plug-circle-bolt', 4, 1),
('Penetration Testing', 'penetration-testing', 'Responsible web, API, auth and hosting-layer security reviews with risk-ranked remediation.', 'Security testing, evidence-based vulnerability reports and remediation guidance.', 'fa-user-secret', 5, 1),
('SEO', 'seo', 'Technical search architecture for high-intent organic demand, structured content and location-aware authority signals.', 'Search systems built around crawlability, intent, authority and conversion.', 'fa-arrow-trend-up', 6, 1);

INSERT INTO products
(slug, title, summary, description, product_type, catalog_category, service_delivery, subtitle, is_featured, platform, platform_tags, price_cents, extended_price_cents, currency, private_file_path, checkout_url, paypal_checkout_url, stripe_price_id, sort_order)
VALUES
(
    'photo-to-key-php-website-backend',
    'Photo To Key PHP Ordering Website With Backend',
    'A camera-first key ordering product with PHP backend, browser upload flow, checkout hooks, shipping, VAT fields and Core Web Vitals-ready UI.',
    'A full Photo To Key webapp for locksmiths and key-duplication services. Customers can access the camera from the browser, take or upload a key photo, add delivery details and place an order through secure payment flows. The package includes a PHP/MySQL backend, order workflow, admin-ready structure, PayPal and Stripe integration points, VAT and shipping fields, mobile-first UI, Trustpilot-style review sections, service blocks, tracking flow and a futuristic Core Web Vitals-ready frontend.',
    'file',
    'php_script',
    NULL,
    'Camera-first ordering webapp with full PHP/MySQL backend',
    1,
    'Core PHP',
    JSON_ARRAY('PHP 8', 'MySQL', 'Browser Camera', 'PayPal', 'Stripe', 'VAT Logic', 'Shipping Flow', 'Core Web Vitals'),
    4900,
    24900,
    'USD',
    'photo-to-key-php-website-backend.zip',
    '/contact?product=photo-to-key',
    '',
    '',
    1
),
(
    'aurora-wordpress-portfolio-theme',
    'Aurora WordPress Portfolio Theme',
    'A responsive creative portfolio and agency WordPress theme with Elementor support, dark UI and WooCommerce-ready layouts.',
    'Aurora is a premium WordPress theme for studios, freelancers and agencies. Includes 12 starter demos, full Elementor compatibility, WooCommerce shop templates, RTL support, one-click demo import and a performance-tuned frontend.',
    'file',
    'wordpress_theme',
    NULL,
    '12 demos, Elementor + WooCommerce ready',
    1,
    'WordPress',
    JSON_ARRAY('WordPress', 'Elementor', 'WooCommerce', 'Responsive', 'RTL', 'One-Click Demo'),
    3900,
    39900,
    'USD',
    NULL,
    NULL,
    '',
    '',
    2
),
(
    'leadflow-wordpress-plugin',
    'LeadFlow Forms & Automation Plugin',
    'A WordPress plugin for smart lead-capture forms, conditional logic, spam protection and CRM/webhook automation.',
    'LeadFlow adds drag-and-drop forms, conditional logic, honeypot + rate-limit spam protection, and native webhook/CRM automation to any WordPress site. Includes Stripe and PayPal payment fields for paid submissions.',
    'file',
    'wordpress_plugin',
    NULL,
    'Smart forms, spam protection and CRM automation',
    0,
    'WordPress',
    JSON_ARRAY('WordPress', 'Forms', 'Automation', 'Webhooks', 'Anti-Spam'),
    2900,
    14900,
    'USD',
    NULL,
    NULL,
    '',
    '',
    3
),
(
    'figma-to-wordpress-service',
    'Figma to WordPress Conversion',
    'Pixel-perfect, responsive, SEO-ready WordPress build from your Figma design — hand-coded or Elementor, your choice.',
    'Send us your Figma file and receive a fully responsive, performance-optimised WordPress site that matches your design pixel-for-pixel. Includes clean markup, on-page SEO, accessibility passes, and either a hand-coded custom theme or an Elementor build.',
    'file',
    'service',
    'figma_to_wordpress',
    'Pixel-perfect, responsive, SEO-ready builds',
    1,
    'WordPress',
    JSON_ARRAY('Figma', 'WordPress', 'Responsive', 'SEO', 'Pixel Perfect'),
    29900,
    NULL,
    'USD',
    NULL,
    '/contact?service=figma-to-wordpress',
    '',
    '',
    4
);

INSERT INTO membership_plans
(slug, name, tagline, price_cents, currency, billing_interval, trial_days, features, badge, cta_label, is_featured, is_active, sort_order)
VALUES
(
    'growth-lab-starter',
    'Growth Lab Starter',
    'Unlimited diagnostics for solo founders and freelancers.',
    1900, 'USD', 'monthly', 7,
    JSON_ARRAY('Unlimited SEO, SERP & security scans', 'White-label PDF reports', 'Standard theme & plugin downloads', 'Email support'),
    NULL, 'Start free trial', 0, 1, 10
),
(
    'growth-lab-pro',
    'Growth Lab Pro',
    'Everything a growing studio needs to ship faster.',
    4900, 'USD', 'monthly', 7,
    JSON_ARRAY('Everything in Starter', 'All premium themes & plugins', 'Figma/PSD-to-WordPress credits', 'Priority build support', 'Continuous background monitoring'),
    'Most popular', 'Get Growth Lab Pro', 1, 1, 20
),
(
    'growth-lab-agency',
    'Growth Lab Agency',
    'Team access, white-label delivery and volume licensing.',
    14900, 'USD', 'monthly', 0,
    JSON_ARRAY('Everything in Pro', 'Extended (agency) licenses included', 'Up to 10 team seats', 'Dedicated Slack channel', 'Quarterly strategy review'),
    NULL, 'Talk to sales', 0, 1, 30
),
(
    'growth-lab-monthly',
    'Growth Lab Pro (Monthly)',
    'Unlimited white-label SEO, security and technical website tools.',
    2500, 'EUR', 'monthly', 0,
    JSON_ARRAY('Unlimited scans across every tool', 'White-label reports with your logo', 'Downloadable client-ready PDF reports', 'On-page, technical SEO and SERP checks', 'Full security + technology scanning', 'Cancel anytime'),
    'Most popular', 'Start monthly', 1, 1, 5
),
(
    'growth-lab-annual',
    'Growth Lab Pro (Yearly)',
    'The full white-label tools suite billed yearly — two months free.',
    20000, 'EUR', 'yearly', 0,
    JSON_ARRAY('Everything in the monthly plan', 'Two months free vs monthly', 'Priority processing', 'Continuous background monitoring', 'Cancel anytime'),
    'Best value', 'Start yearly', 1, 1, 6
);

INSERT INTO faqs (question, answer, category, sort_order) VALUES
('Where is Crest Web Media based?', 'Crest Web Media operates through Dublin, Ireland and Shimla, Himachal Pradesh, India. Phone and WhatsApp: +91 88948 67819.', 'General', 1),
('Can you build both the website and CMS?', 'Yes. Projects can include public pages, admin workflows, media management, blog publishing and SEO controls.', 'CMS', 2),
('Is security testing included?', 'Security best practices are included in every build, with deeper penetration testing available as a dedicated service.', 'Security', 3),
('Will my website be fast?', 'Performance is designed into the build through optimized assets, caching strategy, clean code and Core Web Vitals checks.', 'Performance', 4);
