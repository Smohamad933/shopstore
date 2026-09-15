<?php
/**
 * قالب پنل مدیریت
 * @var string $content
 * @var array $meta
 * @var array|null $adminUser
 */
$stats = admin_stats();
$path = request_path();
$menu = [
    'فروشگاه' => [
        ['/admin', 'داشبورد', 'chart', null],
        ['/admin/orders', 'سفارش‌ها', 'package', $stats['pending_orders'] ?: null],
    ],
    'کاتالوگ' => [
        ['/admin/products', 'محصولات', 'tag', null],
        ['/admin/categories', 'دسته‌بندی‌ها', 'grid', null],
        ['/admin/coupons', 'کدهای تخفیف', 'bolt', null],
    ],
    'کاربران' => [
        ['/admin/customers', 'مشتریان', 'user', null],
        ['/admin/messages', 'پیام‌ها', 'mail', $stats['messages_new'] ?: null],
    ],
    'تنظیمات' => [
        ['/admin/settings', 'تنظیمات فروشگاه', 'settings', null],
    ],
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta['title']) ?> | پنل مدیریت <?= e(setting('site_name')) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#14161a">
    <link rel="icon" href="<?= static_url('/assets/icons/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('/assets/css/admin.css') ?>">
    <script>
        window.SHOP = { base: <?= json_encode(base_path_url()) ?>, csrf: <?= json_encode(csrf_token()) ?>, urls: {} };
    </script>
</head>
<body class="admin">
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <a class="admin-brand" href="<?= url('/admin') ?>">
            <span class="logo-mark"><?= mb_substr(setting('site_name'), 0, 1) ?></span>
            <span><?= e(setting('site_name')) ?><small>پنل مدیریت</small></span>
        </a>

        <nav class="admin-menu">
            <?php foreach ($menu as $section => $links): ?>
                <h5><?= e($section) ?></h5>
                <?php foreach ($links as [$href, $label, $iconName, $badge]): ?>
                    <a href="<?= url($href) ?>" class="<?= $path === $href || ($href !== '/admin' && str_starts_with($path, $href)) ? 'is-active' : '' ?>">
                        <?= icon($iconName) ?>
                        <span><?= e($label) ?></span>
                        <?php if ($badge): ?><span class="count"><?= fa_digits((int) $badge) ?></span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <h5>دسترسی سریع</h5>
            <a href="<?= url('/') ?>" target="_blank"><?= icon('eye') ?> <span>مشاهده سایت</span></a>
            <a href="<?= url('/admin/logout') ?>"><?= icon('logout') ?> <span>خروج</span></a>
        </nav>

        <div class="admin-user">
            <b><?= e($adminUser['name'] ?? 'مدیر') ?></b>
            <span><?= e($adminUser['email'] ?? '') ?></span>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="row">
                <button class="btn btn-sm btn-ghost admin-menu-toggle" type="button" data-admin-menu><?= icon('menu') ?></button>
                <div>
                    <h1><?= e($meta['title']) ?></h1>
                    <div class="crumbs">پنل مدیریت / <?= e($meta['title']) ?></div>
                </div>
            </div>
            <div class="row">
                <span class="pill pill-info"><?= e(order_status_label('processing')) ?> — <?= fa_digits($stats['orders_today']) ?> سفارش امروز</span>
                <a class="btn btn-sm" href="<?= url('/') ?>" target="_blank">مشاهده سایت</a>
            </div>
        </header>

        <main class="admin-content">
            <?php if (!empty($flashItems)): ?>
                <?php foreach ($flashItems as $item): ?>
                    <div class="alert alert-<?= e($item['type'] === 'success' ? 'success' : 'error') ?>">
                        <?= icon($item['type'] === 'success' ? 'check' : 'info') ?>
                        <span><?= e($item['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<div class="admin-sidebar-backdrop" data-admin-menu-backdrop></div>
<div class="toast-stack" id="toastStack" aria-live="polite"></div>

<script>
    document.querySelectorAll('[data-admin-menu]').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('adminSidebar').classList.add('is-open');
            document.querySelector('[data-admin-menu-backdrop]').classList.add('is-open');
        });
    });
    document.querySelectorAll('[data-admin-menu-backdrop]').forEach(function (backdrop) {
        backdrop.addEventListener('click', function () {
            document.getElementById('adminSidebar').classList.remove('is-open');
            backdrop.classList.remove('is-open');
        });
    });
    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
</script>
</body>
</html>
