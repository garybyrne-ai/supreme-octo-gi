<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\MembershipPlanRepository;
use App\Services\AuditLogger;

/**
 * Admin endpoints that give full backend control over membership plans:
 * create, edit, activate/deactivate and delete pricing tiers along with their
 * Stripe and PayPal wiring.
 */
final class AdminCommerceController extends Controller
{
    private const REDIRECT = '/admin/modules/membership';

    public function saveMembershipPlan(): void
    {
        $this->guard();

        try {
            $id = (new MembershipPlanRepository())->save($_POST);
            (new AuditLogger())->log('admin.membership_plan.saved', [
                'id' => $id,
                'slug' => $_POST['slug'] ?? '',
            ]);
            $this->respondSuccess('Membership plan saved.', ['id' => $id]);
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage());
        }
    }

    public function toggleMembershipPlan(): void
    {
        $this->guard();

        try {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id < 1) {
                throw new \RuntimeException('A valid plan id is required.');
            }

            $active = ($_POST['state'] ?? '') === 'activate';
            (new MembershipPlanRepository())->setActive($id, $active);
            (new AuditLogger())->log('admin.membership_plan.toggled', ['id' => $id, 'active' => $active]);
            $this->respondSuccess($active ? 'Plan activated.' : 'Plan paused.');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage());
        }
    }

    public function deleteMembershipPlan(): void
    {
        $this->guard();

        try {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id < 1) {
                throw new \RuntimeException('A valid plan id is required.');
            }

            (new MembershipPlanRepository())->delete($id);
            (new AuditLogger())->log('admin.membership_plan.deleted', ['id' => $id]);
            $this->respondSuccess('Plan deleted.');
        } catch (\Throwable $exception) {
            $this->respondFailure($exception->getMessage());
        }
    }

    private function guard(): void
    {
        Security::ensureSession();

        if (empty($_SESSION['admin'])) {
            $this->redirect('/admin');
        }

        if (!Security::verifyCsrf($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            $_SESSION['admin_error'] = 'Membership session token expired. Please try again.';
            $this->redirect(self::REDIRECT);
        }
    }

    private function respondSuccess(string $message, array $data = []): void
    {
        $_SESSION['admin_notice'] = $message;
        $this->redirect(self::REDIRECT);
    }

    private function respondFailure(string $message): void
    {
        $_SESSION['admin_error'] = $message;
        $this->redirect(self::REDIRECT);
    }
}
