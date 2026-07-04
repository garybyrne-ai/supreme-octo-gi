<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;
use Throwable;

final class InstallerService
{
    public function install(array $input): void
    {
        $db = [
            'driver' => 'mysql',
            'host' => trim((string) $input['db_host']),
            'port' => (int) $input['db_port'],
            'database' => trim((string) $input['db_name']),
            'username' => trim((string) $input['db_user']),
            'password' => (string) $input['db_pass'],
            'charset' => 'utf8mb4',
        ];

        $site = [
            'name' => trim((string) $input['site_name']),
            'url' => rtrim(trim((string) $input['site_url']), '/'),
            'admin_name' => trim((string) $input['admin_name']),
            'admin_email' => trim((string) $input['admin_email']),
        ];

        $adminPassword = (string) $input['admin_password'];

        $this->validate($db, $site, $adminPassword);

        try {
            $pdo = $this->installationConnection($db);
            $this->runSqlFile($pdo, base_path('database/schema.sql'), false);
            $this->runAdditionalMigrations($pdo);
            $this->seedDemoData($pdo, $site, $adminPassword, !empty($input['demo_data']));
            $this->writeInstalledConfig($db, $site);
        } catch (Throwable $exception) {
            throw new RuntimeException('Install failed: ' . $exception->getMessage(), previous: $exception);
        }
    }

    private function validate(array $db, array $site, string $adminPassword): void
    {
        foreach (['host', 'database', 'username'] as $key) {
            if ($db[$key] === '') {
                throw new RuntimeException('Database host, name and username are required.');
            }
        }

        foreach (['name', 'url', 'admin_name', 'admin_email'] as $key) {
            if ($site[$key] === '') {
                throw new RuntimeException('Site name, URL, admin name and admin email are required.');
            }
        }

        if (!filter_var($site['admin_email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Admin email must be valid.');
        }

        if (strlen($adminPassword) < 10) {
            throw new RuntimeException('Admin password must be at least 10 characters.');
        }

        if (!is_writable(base_path('config'))) {
            throw new RuntimeException('The config directory must be writable so the installer can create config/installed.php.');
        }
    }

    private function serverConnection(array $db): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $db['host'], $db['port'], $db['charset']);

        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private function installationConnection(array $db): PDO
    {
        try {
            $server = $this->serverConnection($db);
            $databaseName = str_replace('`', '``', $db['database']);
            $server->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $server->exec("USE `{$databaseName}`");

            return $server;
        } catch (Throwable) {
            return $this->databaseConnection($db);
        }
    }

    private function databaseConnection(array $db): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $db['host'],
            $db['port'],
            $db['database'],
            $db['charset']
        );

        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private function runSqlFile(PDO $pdo, string $path, bool $allowUseStatements): void
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException("Could not read SQL file: {$path}");
        }

        $statements = preg_split('/;\s*[\r\n]+/', $sql) ?: [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            if (!$allowUseStatements && preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $statement)) {
                continue;
            }

            $pdo->exec($statement);
        }
    }

    private function runAdditionalMigrations(PDO $pdo): void
    {
        $migrationDir = base_path('database/migrations');
        if (!is_dir($migrationDir)) {
            return;
        }

        $files = glob($migrationDir . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $file) {
            if (str_starts_with(basename($file), '001_')) {
                continue;
            }

            $this->runSqlFile($pdo, $file, false);
        }
    }

    private function seedDemoData(PDO $pdo, array $site, string $adminPassword, bool $demoData): void
    {
        $pdo->beginTransaction();

        try {
            $pdo->exec("INSERT IGNORE INTO roles (name) VALUES ('Admin'), ('Editor'), ('Author')");

            $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), is_active = 1'
            );
            $stmt->execute([
                'name' => $site['admin_name'],
                'email' => $site['admin_email'],
                'password_hash' => $adminHash,
            ]);

            $userId = (int) $pdo->lastInsertId();
            if ($userId === 0) {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
                $stmt->execute(['email' => $site['admin_email']]);
                $userId = (int) $stmt->fetchColumn();
            }

            $roleId = (int) $pdo->query("SELECT id FROM roles WHERE name = 'Admin'")->fetchColumn();
            $stmt = $pdo->prepare('INSERT IGNORE INTO users_roles (user_id, role_id) VALUES (:user_id, :role_id)');
            $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);

            $settings = $pdo->prepare('INSERT INTO site_settings (group_name, `key`, value) VALUES (:group_name, :key_name, :value) ON DUPLICATE KEY UPDATE value = VALUES(value)');
            foreach ([
                ['site', 'name', $site['name']],
                ['site', 'url', $site['url']],
                ['site', 'tagline', 'Enterprise-style websites, apps, SEO systems and AI workflows for growth-focused teams.'],
                ['contact', 'email', 'ank.kalia@gmail.com'],
                ['contact', 'phone', '+918894867819'],
                ['contact', 'whatsapp', 'https://wa.me/918894867819'],
                ['contact', 'locations', ['Dublin, Ireland', 'Shimla, Himachal Pradesh, India']],
            ] as [$group, $key, $value]) {
                $settings->execute([
                    'group_name' => $group,
                    'key_name' => $key,
                    'value' => json_encode($value),
                ]);
            }

            if ($demoData) {
                $this->insertDemoRows($pdo, $userId);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function insertDemoRows(PDO $pdo, int $authorId): void
    {
        $services = [
            ['Website Development', 'website-development', 'Lean PHP 8/Laravel platforms built for sub-second edge paint, secure CMS control and qualified B2B pipeline.', 'Custom websites and CMS platforms built around performance, security, search visibility and revenue.', 'fa-solid fa-code', 1],
            ['Web Design', 'web-design', 'Conversion-mapped UI systems engineered to reduce cognitive friction and make complex offers easier to buy.', 'Interface design for websites, apps and digital products that need trust, clarity and measurable action.', 'fa-solid fa-wand-magic-sparkles', 2],
            ['App Development', 'app-development', 'Workflow-led web and mobile products with role-aware dashboards, API foundations and resilient data operations.', 'Mobile and web app delivery with data structure, user roles, dashboards and release support.', 'fa-solid fa-mobile-screen-button', 3],
            ['API Integration', 'api-integration', 'Decoupled API and webhook layers for payments, CRMs, booking engines and automation stacks.', 'Secure API integration work for business systems, payments, webhooks, CRMs and automations.', 'fa-solid fa-plug-circle-bolt', 4],
            ['Penetration Testing', 'penetration-testing', 'Responsible web, API, auth and hosting-layer security reviews with risk-ranked remediation.', 'Security testing, evidence-based vulnerability reports and remediation guidance.', 'fa-solid fa-user-secret', 5],
            ['SEO', 'seo', 'Technical search architecture for high-intent organic demand, structured content and location-aware authority signals.', 'Search systems built around crawlability, intent, authority and conversion.', 'fa-solid fa-arrow-trend-up', 6],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO services (title, slug, summary, body, icon, sort_order, is_featured)
             VALUES (?, ?, ?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), body = VALUES(body)'
        );
        foreach ($services as $service) {
            $stmt->execute($service);
        }

        $pages = [
            ['About Crest Web Media', 'about', 'A remote-first web and app studio building secure, search-ready digital systems for growth-focused clients.', 'published'],
            ['Services', 'services', 'Web design, PHP development, ecommerce, apps, API integrations, AI automation, SEO and security for digital systems that need to perform.', 'published'],
            ['Contact', 'contact', 'Contact Crest Web Media for websites, applications, API integrations, SEO systems and AI workflows through WhatsApp, email or support tickets.', 'published'],
            ['Privacy Policy', 'privacy-policy', 'Privacy and data handling information for Crest Web Media.', 'published'],
            ['Terms and Conditions', 'terms-and-conditions', 'Terms for working with Crest Web Media.', 'published'],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO pages (title, slug, body, status, published_at)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body), status = VALUES(status)'
        );
        foreach ($pages as $page) {
            $stmt->execute($page);
        }

        $categories = [
            ['PHP', 'php', 'post'],
            ['SEO', 'seo', 'post'],
            ['Security', 'security', 'post'],
            ['Performance', 'performance', 'post'],
            ['AI Development', 'ai-development', 'post'],
        ];

        $stmt = $pdo->prepare('INSERT IGNORE INTO categories (name, slug, type) VALUES (?, ?, ?)');
        foreach ($categories as $category) {
            $stmt->execute($category);
        }

        $categoryId = (int) $pdo->query("SELECT id FROM categories WHERE slug = 'performance'")->fetchColumn();
        $post = $pdo->prepare(
            'INSERT INTO posts (author_id, category_id, title, slug, excerpt, body, status, reading_time_minutes, published_at)
             VALUES (:author_id, :category_id, :title, :slug, :excerpt, :body, "published", 7, NOW())
             ON DUPLICATE KEY UPDATE title = VALUES(title), excerpt = VALUES(excerpt), body = VALUES(body)'
        );
        $post->execute([
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'title' => 'Core Web Vitals for Business Growth',
            'slug' => 'core-web-vitals-for-business-growth',
            'excerpt' => 'Core Web Vitals are not just technical metrics. They shape trust, conversion rates and organic visibility.',
            'body' => 'A fast website improves trust, lowers friction and helps more visitors become leads. Crest Web Media builds performance into strategy, design, development and launch checks.',
        ]);

        $portfolio = [
            ['Global Travel Platform', 'global-travel-platform', 'Travel', 'Immersive destination content with fast enquiry flows.', 'A tourism website focused on immersive visuals, fast browsing and enquiry conversion.', 'Worldwide', '["PHP","TailwindCSS","SEO"]', '{"lighthouse":98,"conversionLift":"32%"}', 1],
            ['FinTech Dashboard', 'fintech-dashboard', 'Finance', 'Role-aware financial dashboard with clean data hierarchy and decision-grade reporting states.', 'A secure web application dashboard for reporting, charts and operational clarity.', 'Worldwide', '["PHP","MySQL","Chart.js"]', '{"lighthouse":96,"uptime":"99.9%"}', 1],
            ['Healthcare Platform', 'healthcare-platform', 'Healthcare', 'Healthcare workflow interface built around trust, accessibility and structured patient actions.', 'A healthcare platform concept with accessibility, trust and performance at the center.', 'Worldwide', '["PHP","SEO","Security"]', '{"lighthouse":97,"accessibility":100}', 1],
            ['E-Commerce Store', 'e-commerce-store', 'Ecommerce', 'Conversion-focused storefront architecture with product clarity, checkout confidence and performance-led browsing.', 'A scalable online store architecture for products, checkout and analytics.', 'Worldwide', '["WooCommerce","PHP","Core Web Vitals"]', '{"conversionLift":"24%","seo":100}', 1],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO portfolio (title, slug, category, summary, body, client_country, tech_stack, metrics, is_featured, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), body = VALUES(body), metrics = VALUES(metrics)'
        );
        foreach ($portfolio as $item) {
            $stmt->execute($item);
        }

        $testimonials = [
            ['Liam OConnor', 'TravelGrid', 'Worldwide', 'CEO', 5, 'Crest Web Media took the time to understand what our customers needed before touching the design. The finished website feels faster, clearer and far more professional, and the enquiry quality improved because the pages finally explain our offer properly.', 1, 1],
            ['Sarah Johnson', 'FinTechOS', 'Worldwide', 'Founder', 5, 'The web application build was handled with real care. We had dashboards, user flows, forms and admin details that needed to work cleanly, and everything was explained without technical drama. It felt like working with someone who cared about the product, not just the code.', 1, 2],
            ['Thomas Muller', 'StyleHaus', 'Worldwide', 'Owner', 5, 'Our store used to look fine but it did not guide people to buy. Crest Web Media tightened the layout, improved performance and made the product pages easier to trust. The site now feels more premium, and customers tell us checkout is much smoother.', 1, 3],
            ['David Byrne', 'TechSecure', 'Worldwide', 'CTO', 5, 'The security review was practical and easy to act on. Instead of just sending a scary report, they showed us what mattered, what could wait and how to fix the risks properly. It gave our team confidence before pushing the next release live.', 1, 4],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO testimonials (name, company, country, role, rating, quote, is_featured, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($testimonials as $testimonial) {
            $stmt->execute($testimonial);
        }

        $faq = $pdo->prepare('INSERT INTO faqs (question, answer, category, sort_order) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE answer = VALUES(answer)');
        foreach ([
            ['Where is Crest Web Media based?', 'Crest Web Media operates through Dublin, Ireland and Shimla, Himachal Pradesh, India, serving clients remotely across the globe through email, WhatsApp and support tickets.', 'General', 1],
            ['Can you build both the website and CMS?', 'Yes. Projects can include public pages, admin workflows, media management, blog publishing and SEO controls.', 'CMS', 2],
            ['Is security testing included?', 'Security best practices are included in every build, with deeper penetration testing available as a dedicated service.', 'Security', 3],
        ] as $row) {
            $faq->execute($row);
        }

        $menu = $pdo->prepare('INSERT INTO menus (location, label, url, sort_order) VALUES (?, ?, ?, ?)');
        foreach ([
            ['primary', 'Home', '/', 1],
            ['primary', 'About', '/about', 2],
            ['primary', 'Services', '/services', 3],
            ['primary', 'Portfolio', '/portfolio', 4],
            ['primary', 'Blog', '/blog', 5],
            ['primary', 'Contact', '/contact', 6],
        ] as $item) {
            $menu->execute($item);
        }
    }

    private function writeInstalledConfig(array $db, array $site): void
    {
        $payload = [
            'installed_at' => gmdate('c'),
            'site' => [
                'name' => $site['name'],
                'url' => $site['url'],
                'admin_email' => $site['admin_email'],
            ],
            'database' => $db,
        ];

        $php = "<?php\n\nreturn " . var_export($payload, true) . ";\n";
        $target = base_path('config/installed.php');

        if (file_put_contents($target, $php, LOCK_EX) === false) {
            throw new RuntimeException('Could not write config/installed.php.');
        }
    }
}
