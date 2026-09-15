<?php
/**
 * منوی کشویی موبایل
 * @var array $navCategories
 * @var array|null $user
 */
?>
<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-backdrop" data-drawer-close></div>
    <aside class="drawer-panel" role="dialog" aria-label="منوی سایت">
        <div class="drawer-head">
            <b><?= e(setting('site_name')) ?></b>
            <button class="icon-btn" type="button" data-drawer-close aria-label="بستن"><?= icon('close') ?></button>
        </div>

        <?php if (is_logged_in()): ?>
            <div class="drawer-section">
                <div class="row">
                    <span class="avatar"><?= e(mb_substr((string) $user['name'], 0, 1)) ?></span>
                    <div class="grow">
                        <b><?= e($user['name']) ?></b>
                        <div class="tiny muted"><?= e($user['email']) ?></div>
                    </div>
                </div>
                <div class="row mt-1">
                    <a class="btn btn-sm grow" href="<?= url('/account') ?>">پنل کاربری</a>
                    <a class="btn btn-sm btn-ghost" href="<?= url('/logout') ?>">خروج</a>
                </div>
            </div>
        <?php else: ?>
            <div class="drawer-section">
                <a class="btn btn-primary btn-block" href="<?= url('/login') ?>">ورود / ثبت‌نام</a>
                <p class="tiny muted mt-1 mb-0">با ورود به حساب، سفارش‌های خود را پیگیری کنید.</p>
            </div>
        <?php endif; ?>

        <div class="drawer-section">
            <h4>دسته‌بندی‌ها</h4>
            <div class="drawer-list">
                <?php foreach ($navCategories as $category): ?>
                    <a href="<?= url(category_url($category)) ?>">
                        <span><?= e($category['icon']) ?> <?= e($category['name']) ?></span>
                        <span class="count"><?= fa_digits((int) $category['product_count']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="drawer-section">
            <h4>دسترسی سریع</h4>
            <div class="drawer-list">
                <a href="<?= url('/discounts') ?>"><span>پیشنهادهای ویژه</span><span>٪</span></a>
                <a href="<?= url('/track') ?>"><span>پیگیری سفارش</span><span>›</span></a>
                <a href="<?= url('/about') ?>"><span>درباره ما</span><span>›</span></a>
                <a href="<?= url('/faq') ?>"><span>سؤالات متداول</span><span>›</span></a>
                <a href="<?= url('/terms') ?>"><span>قوانین و مقررات</span><span>›</span></a>
                <a href="<?= url('/contact') ?>"><span>تماس با ما</span><span>›</span></a>
                <a href="<?= url('/admin') ?>"><span>ورود مدیران</span><span>›</span></a>
            </div>
        </div>

        <div class="drawer-section">
            <h4>ارتباط با ما</h4>
            <div class="row">
                <?= icon('phone') ?>
                <a href="tel:<?= e(en_digits(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>
            </div>
            <div class="row mt-1">
                <?= icon('clock') ?>
                <span class="small muted"><?= e(setting('work_hours')) ?></span>
            </div>
            <button class="btn btn-block mt-2 hidden" type="button" data-install-app>
                <?= icon('download') ?> نصب اپلیکیشن
            </button>
        </div>
    </aside>
</div>
