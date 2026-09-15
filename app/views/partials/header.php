<?php
/**
 * هدر سایت: نوار بالایی، جست‌وجو، اکشن‌ها و منوی دسته‌بندی‌ها
 * @var array $navCategories
 * @var int $cartCount
 * @var array|null $user
 */
$activePath = request_path();
?>
<div class="top-strip">
    <div class="container">
        <div class="strip-announce">
            <?= icon('truck') ?>
            <span><?= e(setting('announcement')) ?></span>
        </div>
        <div class="strip-links">
            <a href="<?= url('/track') ?>">پیگیری سفارش</a>
            <a href="<?= url('/faq') ?>">سؤالات متداول</a>
            <a href="<?= url('/contact') ?>">تماس با ما</a>
            <a href="tel:<?= e(en_digits(setting('phone'))) ?>"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="container header-main">
        <button class="icon-btn burger" type="button" data-drawer-open aria-label="منو">
            <?= icon('menu') ?>
        </button>

        <a class="logo" href="<?= url('/') ?>" aria-label="<?= e(setting('site_name')) ?>">
            <span class="logo-mark">س</span>
            <span>
                <?= e(setting('site_name')) ?>
                <small><?= e(setting('site_tagline')) ?></small>
            </span>
        </a>

        <form class="search-form" action="<?= url('/search') ?>" method="get" role="search" data-search-form>
            <?= icon('search', 'search-icon') ?>
            <input class="input" type="search" name="q" placeholder="جست‌وجو در محصولات، برند یا کد کالا…"
                   value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" aria-label="جست‌وجو">
            <button class="search-submit" type="submit">جست‌وجو</button>
            <div class="suggestions" hidden></div>
        </form>

        <div class="header-actions">
            <button class="icon-btn install-btn hidden" type="button" data-install-app title="نصب روی گوشی">
                <?= icon('download') ?><span class="label-text">نصب اپلیکیشن</span>
            </button>

            <a class="icon-btn" href="<?= url(is_logged_in() ? '/account' : '/login') ?>" title="حساب کاربری">
                <?= icon('user') ?>
                <span class="label-text"><?= is_logged_in() ? e(explode(' ', (string) $user['name'])[0]) : 'ورود / ثبت‌نام' ?></span>
            </a>

            <a class="icon-btn" href="<?= url('/cart') ?>" title="سبد خرید">
                <?= icon('cart') ?>
                <span class="label-text">سبد خرید</span>
                <span class="cart-count<?= $cartCount > 0 ? '' : ' hidden' ?>" data-cart-count><?= fa_digits($cartCount) ?></span>
            </a>
        </div>
    </div>

    <div class="container mobile-search" style="display:none; padding-bottom:10px;">
        <form class="search-form" action="<?= url('/search') ?>" method="get" role="search" data-search-form style="display:block; max-width:none;">
            <?= icon('search', 'search-icon') ?>
            <input class="input" type="search" name="q" placeholder="جست‌وجو در محصولات…" value="<?= e($_GET['q'] ?? '') ?>">
            <div class="suggestions" hidden></div>
        </form>
    </div>

    <nav class="nav-bar" aria-label="دسته‌بندی‌ها">
        <div class="container">
            <a class="nav-link <?= $activePath === '/' ? 'is-active' : '' ?>" href="<?= url('/') ?>">صفحه اصلی</a>
            <?php foreach ($navCategories as $category): ?>
                <a class="nav-link <?= str_starts_with($activePath, '/category/' . $category['id'] . '-') ? 'is-active' : '' ?>"
                   href="<?= url(category_url($category)) ?>"><?= e($category['name']) ?></a>
            <?php endforeach; ?>
            <a class="nav-link is-special" href="<?= url('/discounts') ?>">تخفیف‌ها</a>
            <a class="nav-link" href="<?= url('/contact') ?>">تماس و پشتیبانی</a>
        </div>
    </nav>
</header>
