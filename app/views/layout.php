<?php
/**
 * قالب اصلی سایت
 * @var string $content
 * @var array $meta
 */
$meta = $meta ?? [];
$pageTitle = ($meta['title'] ?? null) ? $meta['title'] . ' | ' . $siteName : $siteName . ' — ' . setting('site_tagline');
$description = $meta['description'] ?? setting('site_description');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <?php if (!empty($meta['no_index'])): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <link rel="canonical" href="<?= e($meta['canonical'] ?? absolute_url(request_path())) ?>">
    <?php endif; ?>

    <!-- شبکه‌های اجتماعی -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:image" content="<?= e($meta['og_image'] ?? static_url('/assets/img/hero-1.jpg')) ?>">
    <meta name="twitter:card" content="summary_large_image">

    <!-- PWA -->
    <meta name="theme-color" content="#0f766e">
    <meta name="color-scheme" content="light">
    <link rel="manifest" href="<?= static_url('/manifest.webmanifest') ?>">
    <link rel="icon" href="<?= static_url('/assets/icons/favicon.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= static_url('/assets/icons/icon-192.png') ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?= e($siteName) ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="preload" href="<?= static_url('/assets/fonts/Vazirmatn-Regular.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= static_url('/assets/fonts/Vazirmatn-SemiBold.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <script>
        window.SHOP = {
            base: <?= json_encode(base_path_url(), JSON_UNESCAPED_SLASHES) ?>,
            staticBase: <?= json_encode(static_base_url(), JSON_UNESCAPED_SLASHES) ?>,
            urls: {
                cart: <?= json_encode(url('/api/cart')) ?>,
                cartAdd: <?= json_encode(url('/cart/add')) ?>,
                cartUpdate: <?= json_encode(url('/cart/update')) ?>,
                cartRemove: <?= json_encode(url('/cart/remove')) ?>,
                search: <?= json_encode(url('/api/search')) ?>,
                cartCoupon: <?= json_encode(url('/cart/coupon')) ?>
            },
            csrf: <?= json_encode(csrf_token()) ?>
        };
    </script>
</head>
<body class="<?= e($meta['body_class'] ?? '') ?>">
<a class="skip-link" href="#main">رفتن به محتوای اصلی</a>

<?php partial('header', ['navCategories' => $navCategories, 'cartCount' => $cartCount, 'user' => $user]); ?>

<main id="main" class="page">
    <?php partial('flash', ['flashItems' => $flashItems ?? []]); ?>
    <?= $content ?>
</main>

<?php partial('footer', ['navCategories' => $navCategories]); ?>
<?php partial('bottom-nav', ['cartCount' => $cartCount, 'user' => $user]); ?>
<?php partial('drawer', ['navCategories' => $navCategories, 'user' => $user]); ?>

<div class="toast-stack" id="toastStack" aria-live="polite"></div>
<div class="offline-banner" id="offlineBanner" hidden>اتصال اینترنت قطع است — نسخه ذخیره‌شده سایت نمایش داده می‌شود.</div>

<script src="<?= asset('/assets/js/app.js') ?>" defer></script>
<script src="<?= asset('/assets/js/pwa.js') ?>" defer></script>
</body>
</html>
