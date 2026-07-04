<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Services\InstallerService;
use Throwable;

final class InstallController extends Controller
{
    public function index(): void
    {
        if ($this->installed()) {
            $this->render('install/success', [
                'title' => 'Already Installed | Crest Web Media',
                'metaDescription' => 'Crest Web Media is already installed.',
            ]);
            return;
        }

        $this->render('install/index', [
            'title' => 'Install Crest Web Media',
            'metaDescription' => 'Install Crest Web Media CMS and demo data.',
            'csrf' => Security::csrfToken(),
            'installToken' => $this->installToken(),
            'old' => $this->defaults(),
        ]);
    }

    public function store(): void
    {
        if ($this->installed()) {
            $this->redirect('/');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null) && !$this->verifyInstallToken((string) ($_POST['_install_token'] ?? ''))) {
            http_response_code(419);
            $this->render('install/index', [
                'title' => 'Install Crest Web Media',
                'metaDescription' => 'Install Crest Web Media CMS and demo data.',
                'csrf' => Security::csrfToken(),
                'installToken' => $this->installToken(),
                'old' => $this->safeOld($_POST),
                'error' => 'The install form expired. Please try again.',
            ]);
            return;
        }

        try {
            (new InstallerService())->install($_POST);
            $this->render('install/success', [
                'title' => 'Install Complete | Crest Web Media',
                'metaDescription' => 'Crest Web Media was installed successfully.',
                'adminEmail' => trim((string) ($_POST['admin_email'] ?? '')),
            ]);
        } catch (Throwable $exception) {
            http_response_code(422);
            $this->render('install/index', [
                'title' => 'Install Crest Web Media',
                'metaDescription' => 'Install Crest Web Media CMS and demo data.',
                'csrf' => Security::csrfToken(),
                'installToken' => $this->installToken(),
                'old' => $this->safeOld($_POST),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function installed(): bool
    {
        return is_file(base_path('config/installed.php'));
    }

    private function defaults(): array
    {
        return [
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_name' => 'crest_web_media',
            'db_user' => 'root',
            'db_pass' => '',
            'site_name' => 'Crest Web Media',
            'site_url' => 'http://localhost:8080',
            'admin_name' => 'Crest Admin',
            'admin_email' => 'admin@crestwebmedia.com',
            'demo_data' => '1',
        ];
    }

    private function safeOld(array $input): array
    {
        $demoDataChecked = array_key_exists('demo_data', $input);
        unset($input['db_pass'], $input['admin_password'], $input['_csrf'], $input['_install_token']);
        $old = $input + $this->defaults();
        $old['demo_data'] = $demoDataChecked ? '1' : '';
        return $old;
    }

    private function installToken(): string
    {
        $timestamp = (string) time();
        return $timestamp . ':' . hash_hmac('sha256', $timestamp . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? ''), $this->installTokenSecret());
    }

    private function verifyInstallToken(string $token): bool
    {
        [$timestamp, $hash] = array_pad(explode(':', $token, 2), 2, '');
        if (!ctype_digit($timestamp) || time() - (int) $timestamp > 3600) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? ''), $this->installTokenSecret());
        return hash_equals($expected, $hash);
    }

    private function installTokenSecret(): string
    {
        return hash('sha256', base_path('config/app.php') . '|' . __FILE__);
    }
}
