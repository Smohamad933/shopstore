<?php
/**
 * نوار ناوبری پایین صفحه (فقط موبایل) — مخصوص تجربه PWA
 */
$activePath = request_path();
$isActive = fn (string $prefix) => $prefix === '/' ? $activePath === '/' : str_starts_with($activePath, $prefix);
?>
<nav class="bottom-nav" aria-label="ناوبری سریع">
    <div class="items">
        <a href="<?= url('/') ?>" class="<?= $isActive('/') ? 'is-active' : '' ?>">
            <?= icon('home') ?>
            <span>خانه</span>
        </a>
        <a href="<?= url('/discounts') ?>" class="<?= $isActive('/discounts') ? 'is-active' : '' ?>">
            <?= icon('bolt') ?>
            <span>تخفیف‌ها</span>
        </a>
        <a href="<?= url('/search') ?>" class="<?= $isActive('/search') ? 'is-active' : '' ?>">
            <?= icon('search') ?>
            <span>جست‌وجو</span>
        </a>
        <a href="<?= url('/cart') ?>" class="<?= $isActive('/cart') ? 'is-active' : '' ?>">
            <span class="badge-wrap">
                <?= icon('cart') ?>
                <span class="cart-count<?= $cartCount > 0 ? '' : ' hidden' ?>" data-cart-count><?= fa_digits($cartCount) ?></span>
            </span>
            <span>سبد خرید</span>
        </a>
        <a href="<?= url(is_logged_in() ? '/account' : '/login') ?>" class="<?= $isActive('/account') ? 'is-active' : '' ?>">
            <?= icon('user') ?>
            <span>حساب من</span>
        </a>
    </div>
</nav>
