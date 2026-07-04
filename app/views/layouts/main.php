<?php
$title = $title ?? $config['name'];
$metaDescription = $metaDescription ?? $config['tagline'];
$cmsTypographyStyle = '';
$cmsHeadInjections = '';
$cmsBodyOpenInjections = '';
$cmsFooterCloseInjections = '';
$cmsCustomCssLinks = '';
$cmsCustomJsScripts = '';

if (is_file(base_path('config/installed.php'))) {
    try {
        $cmsTypographyStyle = (new \App\Services\TypographyEngine())->cachedStyleBlock();
        $customCode = new \App\Services\CustomCodeCompiler();
        $cmsCustomCssLinks = $customCode->renderCssLinks();
        $cmsCustomJsScripts = $customCode->renderJsScripts();
        $scriptGuard = new \App\Services\ScriptInjectionGuard();
        $cmsHeadInjections = $scriptGuard->render('head');
        $cmsBodyOpenInjections = $scriptGuard->render('body_open');
        $cmsFooterCloseInjections = $scriptGuard->render('footer_close');
    } catch (\Throwable) {
        $cmsTypographyStyle = '';
        $cmsHeadInjections = '';
        $cmsBodyOpenInjections = '';
        $cmsFooterCloseInjections = '';
        $cmsCustomCssLinks = '';
        $cmsCustomJsScripts = '';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="theme-color" content="#04070D">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:type" content="website">
    <link rel="preload" href="<?= asset('images/hero-himalayan-studio.webp') ?>" as="image" type="image/webp" fetchpriority="high">
    <link rel="preload" href="<?= asset('fonts/oxanium-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= asset('fonts/raleway-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/style.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shop.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/commerce-suite.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/enterprise-refinements.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <?= $cmsTypographyStyle ?>
    <?= $cmsCustomCssLinks ?>
    <?= $cmsHeadInjections ?>
    <?php if (!empty($schema)): ?>
        <script type="application/ld+json"><?= $schema ?></script>
    <?php endif; ?>
</head>
<body>
<?= $cmsBodyOpenInjections ?>
<div class="scroll-progress" aria-hidden="true"></div>
<div class="cursor-glow" aria-hidden="true"></div>
<div class="site-shell">
    <?php require base_path('app/views/partials/header.php'); ?>
    <main>
        <?= $content ?>
    </main>
    <?php require base_path('app/views/partials/footer.php'); ?>
</div>
<?= $cmsFooterCloseInjections ?>
<script src="<?= asset('js/app.js') ?>" defer></script>
<?= $cmsCustomJsScripts ?>
</body>
</html>
