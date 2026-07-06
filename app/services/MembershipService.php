<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\MemberRepository;

/**
 * Central membership activation used by the Stripe and PayPal webhooks.
 * Matches a paying customer to a site member by email so hosted checkouts can
 * flip an account to Pro, and mirrors the state into the commerce tables when
 * a database is available.
 */
final class MembershipService
{
    public function __construct(private readonly MemberRepository $members = new MemberRepository())
    {
    }

    public function activate(
        string $email,
        string $planCode,
        string $provider,
        ?string $providerReference = null,
        ?string $currentPeriodEndsAt = null
    ): bool {
        $email = strtolower(trim($email));
        if ($email === '') {
            return false;
        }

        $membership = [
            'status' => 'active',
            'plan_code' => $planCode !== '' ? $planCode : 'growth-lab',
            'provider' => in_array($provider, ['stripe', 'paypal', 'manual'], true) ? $provider : 'manual',
            'provider_reference' => $providerReference,
            'current_period_ends_at' => $currentPeriodEndsAt,
            'updated_at' => gmdate('c'),
        ];

        $linkedToMember = $this->members->setMembershipByEmail($email, $membership);

        $this->syncSubscriptionRow($email, $membership, 'active');
        $this->syncSignupStatus($email, $provider, 'active');
        (new AuditLogger())->log('membership.activated', [
            'email' => $email,
            'plan' => $membership['plan_code'],
            'provider' => $membership['provider'],
            'linked_member' => $linkedToMember,
        ]);

        return true;
    }

    public function deactivate(string $email, string $provider, string $status = 'cancelled'): bool
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return false;
        }

        $member = $this->members->findByEmail($email);
        if ($member !== null && is_array($member['membership'] ?? null)) {
            $membership = $member['membership'];
            $membership['status'] = $status === 'expired' ? 'expired' : 'cancelled';
            $membership['updated_at'] = gmdate('c');
            $this->members->setMembershipByEmail($email, $membership);
        }

        $this->syncSubscriptionRow($email, ['plan_code' => '', 'provider' => $provider, 'provider_reference' => null, 'current_period_ends_at' => null], $status === 'expired' ? 'expired' : 'cancelled');
        (new AuditLogger())->log('membership.deactivated', ['email' => $email, 'provider' => $provider, 'status' => $status]);

        return true;
    }

    /**
     * @param array<string, mixed> $membership
     */
    private function syncSubscriptionRow(string $email, array $membership, string $status): void
    {
        try {
            $pdo = Database::connection();
            $customer = $pdo->prepare('SELECT id FROM commerce_customers WHERE email = :email LIMIT 1');
            $customer->execute(['email' => $email]);
            $customerId = (int) $customer->fetchColumn();

            if ($customerId < 1) {
                $insert = $pdo->prepare('INSERT INTO commerce_customers (email) VALUES (:email)');
                $insert->execute(['email' => $email]);
                $customerId = (int) $pdo->lastInsertId();
            }

            $provider = in_array($membership['provider'] ?? 'stripe', ['stripe', 'paypal'], true) ? $membership['provider'] : 'stripe';
            $reference = (string) ($membership['provider_reference'] ?? ('manual-' . substr(hash('sha256', $email . $status), 0, 24)));

            $stmt = $pdo->prepare(
                'INSERT INTO commerce_subscriptions
                    (customer_id, provider, provider_subscription_id, plan_code, status, current_period_ends_at)
                 VALUES (:customer_id, :provider, :provider_subscription_id, :plan_code, :status, :ends_at)
                 ON DUPLICATE KEY UPDATE status = VALUES(status), plan_code = VALUES(plan_code), current_period_ends_at = VALUES(current_period_ends_at)'
            );
            $stmt->execute([
                'customer_id' => $customerId,
                'provider' => $provider,
                'provider_subscription_id' => $reference,
                'plan_code' => (string) ($membership['plan_code'] ?? 'growth-lab'),
                'status' => $status,
                'ends_at' => $membership['current_period_ends_at'] ?? null,
            ]);
        } catch (\Throwable) {
            // DB not installed or table missing: member-file membership is the source of truth.
        }
    }

    private function syncSignupStatus(string $email, string $provider, string $status): void
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'UPDATE membership_signups
                 SET status = :status, provider = :provider, updated_at = UTC_TIMESTAMP()
                 WHERE email = :email'
            );
            $stmt->execute([
                'status' => $status,
                'provider' => in_array($provider, ['stripe', 'paypal', 'manual'], true) ? $provider : 'manual',
                'email' => $email,
            ]);
        } catch (\Throwable) {
        }
    }
}
