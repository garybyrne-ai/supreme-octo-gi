<?php
/** @var array<int, array<string,mixed>> $plans */
/** @var \App\Models\MembershipPlanRepository $planRepo */
$plans = $plans ?? [];
?>
<section class="subhero membership-hero">
    <span class="status-chip"><span></span> Growth Lab Membership</span>
    <h1>One membership. Every tool, theme &amp; plugin.</h1>
    <p>Unlock unlimited security &amp; SEO diagnostics, premium WordPress themes and plugins, and priority build support — billed the way that suits you and cancellable anytime.</p>
</section>

<section class="section reveal">
    <?php if (!empty($plans)): ?>
        <div class="membership-grid">
            <?php foreach ($plans as $plan): ?>
                <?php
                $hasStripe = !empty($plan['stripe_payment_link']) || !empty($plan['stripe_price_id']);
                $hasPaypal = !empty($plan['paypal_subscribe_url']) || !empty($plan['paypal_plan_id']);
                $intervalLabel = $planRepo->intervalLabel((string) $plan['billing_interval']);
                ?>
                <article class="membership-card cyber-card <?= !empty($plan['is_featured']) ? 'is-featured' : '' ?>">
                    <?php if (!empty($plan['badge'])): ?>
                        <span class="membership-badge"><?= e($plan['badge']) ?></span>
                    <?php endif; ?>
                    <header class="membership-card-head">
                        <h2><?= e($plan['name']) ?></h2>
                        <?php if (!empty($plan['tagline'])): ?><p><?= e($plan['tagline']) ?></p><?php endif; ?>
                    </header>
                    <div class="membership-card-price">
                        <strong><?= e($planRepo->formatPrice($plan)) ?></strong>
                        <?php if ($intervalLabel !== ''): ?><span><?= e($intervalLabel) ?></span><?php endif; ?>
                    </div>
                    <?php if (!empty($plan['trial_days'])): ?>
                        <p class="membership-trial"><i class="fa-solid fa-bolt"></i> <?= e((string) $plan['trial_days']) ?>-day free trial</p>
                    <?php endif; ?>
                    <?php if (!empty($plan['features'])): ?>
                        <ul class="check-list">
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li><?= e($feature) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <div class="membership-card-actions">
                        <?php if ($hasStripe || $hasPaypal): ?>
                            <form method="post" action="/membership/join" class="membership-join-form">
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                                <label class="membership-email">Email for receipts
                                    <input name="email" type="email" autocomplete="email" placeholder="you@company.com">
                                </label>
                                <?php if ($hasStripe): ?>
                                    <button class="pill-button" type="submit" name="gateway" value="stripe">
                                        <?= e($plan['cta_label'] ?? 'Get started') ?> with Card <i class="fa-solid fa-credit-card"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($hasPaypal): ?>
                                    <button class="pill-button ghost" type="submit" name="gateway" value="paypal">
                                        Pay with PayPal <i class="fa-brands fa-paypal"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <a class="pill-button ghost" href="/contact?plan=<?= e($plan['slug']) ?>">Talk to us <i class="fa-solid fa-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="cyber-card membership-empty">
            <h2>Membership plans are being finalised.</h2>
            <p>Our Growth Lab membership is launching shortly. Tell us what you need and we'll give you early access and founding-member pricing.</p>
            <a class="pill-button" href="/contact">Request early access <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    <?php endif; ?>
</section>

<section class="split-section reveal">
    <div>
        <h2>Why members build faster</h2>
        <p>A Crest Web Media membership bundles the tools, digital products and expert support you'd otherwise buy separately — so every project starts further ahead.</p>
    </div>
    <div class="cyber-card">
        <h2>Included with every plan</h2>
        <ul class="check-list">
            <li>Unlimited access to the Growth Lab tool suite</li>
            <li>Premium theme &amp; plugin marketplace downloads</li>
            <li>Secure, licensed, count-limited download delivery</li>
            <li>Cancel or switch tiers anytime from your receipts</li>
        </ul>
    </div>
</section>
