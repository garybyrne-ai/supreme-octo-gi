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
(slug, title, summary, description, product_type, platform, platform_tags, price_cents, currency, private_file_path, checkout_url, paypal_checkout_url, stripe_price_id, sort_order)
VALUES
(
    'photo-to-key-php-website-backend',
    'Photo To Key PHP Ordering Website With Backend',
    'A camera-first key ordering product with PHP backend, browser upload flow, checkout hooks, shipping, VAT fields and Core Web Vitals-ready UI.',
    'A full Photo To Key webapp for locksmiths and key-duplication services. Customers can access the camera from the browser, take or upload a key photo, add delivery details and place an order through secure payment flows. The package includes a PHP/MySQL backend, order workflow, admin-ready structure, PayPal and Stripe integration points, VAT and shipping fields, mobile-first UI, Trustpilot-style review sections, service blocks, tracking flow and a futuristic Core Web Vitals-ready frontend.',
    'file',
    'Core PHP',
    JSON_ARRAY('PHP 8', 'MySQL', 'Browser Camera', 'PayPal', 'Stripe', 'VAT Logic', 'Shipping Flow', 'Core Web Vitals'),
    4900,
    'USD',
    'photo-to-key-php-website-backend.zip',
    '/contact?product=photo-to-key',
    '',
    '',
    1
);

INSERT INTO faqs (question, answer, category, sort_order) VALUES
('Where is Crest Web Media based?', 'Crest Web Media operates through Dublin, Ireland and Shimla, Himachal Pradesh, India. Phone and WhatsApp: +91 88948 67819.', 'General', 1),
('Can you build both the website and CMS?', 'Yes. Projects can include public pages, admin workflows, media management, blog publishing and SEO controls.', 'CMS', 2),
('Is security testing included?', 'Security best practices are included in every build, with deeper penetration testing available as a dedicated service.', 'Security', 3),
('Will my website be fast?', 'Performance is designed into the build through optimized assets, caching strategy, clean code and Core Web Vitals checks.', 'Performance', 4);
