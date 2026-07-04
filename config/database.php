<?php

declare(strict_types=1);

namespace Config;

use PDO;

if (!class_exists(Database::class, false)) {
    final class Database
    {
        private static ?PDO $pdo = null;

        public static function connection(): PDO
        {
            if (self::$pdo instanceof PDO) {
                return self::$pdo;
            }

            $config = self::settings();
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                (int) $config['port'],
                $config['database'],
                $config['charset'] ?? 'utf8mb4'
            );

            self::$pdo = new PDO($dsn, (string) $config['username'], (string) $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return self::$pdo;
        }

        public static function settings(): array
        {
            $root = \defined('BASE_PATH') ? (string) \constant('BASE_PATH') : \dirname(__DIR__);
            $installed = $root . '/config/installed.php';

            if (\is_file($installed)) {
                $config = require $installed;
                return $config['database'];
            }

            return [
                'driver' => 'mysql',
                'host' => \getenv('DB_HOST') ?: '127.0.0.1',
                'port' => (int) (\getenv('DB_PORT') ?: 3306),
                'database' => \getenv('DB_DATABASE') ?: 'crest_web_media',
                'username' => \getenv('DB_USERNAME') ?: 'root',
                'password' => \getenv('DB_PASSWORD') ?: '',
                'charset' => 'utf8mb4',
            ];
        }

        private function __construct()
        {
        }
    }
}

return Database::settings();
