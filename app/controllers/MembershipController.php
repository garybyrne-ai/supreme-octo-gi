<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\MembershipPlanRepository;
use App\Services\AuditLogger;

final class MembershipController extends Controller
{
    public function index(): void
    {
        $repo = new MembershipPlanRepository();

        $this->render('pages/membership', [
            'title' => 'Membership | Crest Web Media Growth Lab',
            'metaDescription' => 'Join a Crest Web Media membership plan for unlimited tools, premium themes and plugins, and priority build support.',
            'plans' => $repo->activePlans(),
            'planRepo' => $repo,
            'csrf' => Security::csrfToken(),
        ]);
    }

    public function join(): void
    {
        $repo = new MembershipPlanRepository();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $this->redirect('/membership');
        }

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $plan = $repo->find($planId);
        if ($plan === null || (int) $plan['is_active'] !== 1) {
            $this->redirect('/membership');
        }

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $gateway = (string) ($_POST['gateway'] ?? 'stripe');

        $target = $gateway === 'paypal'
            ? (string) ($plan['paypal_subscribe_url'] ?? '')
            : (string) ($plan['stripe_payment_link'] ?? '');

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $repo->recordSignup($planId, $email, (string) ($_POST['name'] ?? ''), $gateway === 'paypal' ? 'paypal' : 'stripe');
                (new AuditLogger())->log('membership.signup.intent', [
                    'plan_id' => $planId,
                    'gateway' => $gateway,
                ]);
            } catch (\Throwable) {
                // Signup logging is best-effort; never block the checkout redirect.
            }
        }

        if ($target !== '' && filter_var($target, FILTER_VALIDATE_URL)) {
            $this->redirect($target);
        }

        // No gateway link configured yet: send the visitor to contact with context.
        $this->redirect('/contact?plan=' . urlencode((string) $plan['slug']));
    }
}
