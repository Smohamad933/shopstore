<?php
/**
 * فوتر سایت
 * @var array $navCategories
 */
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a class="logo mb-2" href="<?= url('/') ?>">
                    <span class="logo-mark">س</span>
                    <span><?= e(setting('site_name')) ?><small><?= e(setting('site_tagline')) ?></small></span>
                </a>
                <p class="small muted"><?= e(mb_substr(setting('about_text'), 0, 190)) ?>…</p>
                <div class="social-row">
                    <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" rel="noopener" target="_blank" aria-label="اینستاگرام"><?= icon('instagram') ?></a><?php endif; ?>
                    <?php if (setting('telegram')): ?><a href="<?= e(setting('telegram')) ?>" rel="noopener" target="_blank" aria-label="تلگرام"><?= icon('telegram') ?></a><?php endif; ?>
                    <?php if (setting('whatsapp')): ?><a href="<?= e(setting('whatsapp')) ?>" rel="noopener" target="_blank" aria-label="واتس‌اپ"><?= icon('whatsapp') ?></a><?php endif; ?>
                </div>
            </div>

            <div>
                <h4>دسته‌بندی‌ها</h4>
                <ul>
                    <?php foreach (array_slice($navCategories, 0, 6) as $category): ?>
                        <li><a href="<?= url(category_url($category)) ?>"><?= e($category['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h4>خدمات مشتریان</h4>
                <ul>
                    <li><a href="<?= url('/track') ?>">پیگیری سفارش</a></li>
                    <li><a href="<?= url('/faq') ?>">سؤالات متداول</a></li>
                    <li><a href="<?= url('/terms') ?>">شرایط ارسال و بازگشت کالا</a></li>
                    <li><a href="<?= url('/about') ?>">درباره <?= e(setting('site_name')) ?></a></li>
                    <li><a href="<?= url('/contact') ?>">تماس و پشتیبانی فنی</a></li>
                    <li><a href="<?= url('/sitemap.xml') ?>">نقشه سایت</a></li>
                </ul>
            </div>

            <div>
                <h4>تماس با ما</h4>
                <ul class="footer-contact">
                    <li><?= icon('pin') ?><span><?= e(setting('address')) ?></span></li>
                    <li><?= icon('phone') ?><a href="tel:<?= e(en_digits(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
                    <li><?= icon('mail') ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
                    <li><?= icon('clock') ?><span><?= e(setting('work_hours')) ?></span></li>
                </ul>
                <button class="btn btn-sm mt-1 hidden" type="button" data-install-app>
                    <?= icon('download') ?> نصب اپلیکیشن روی گوشی
                </button>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© <?= fa_digits(gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'))[0]) ?> — تمامی حقوق برای <?= e(setting('site_name')) ?> محفوظ است.</span>
            <div class="footer-trust">
                <span>✓ پرداخت امن</span>
                <span>✓ ضمانت اصالت کالا</span>
                <span>✓ ۷ روز بازگشت کالا</span>
                <span>✓ ارسال به سراسر ایران</span>
            </div>
        </div>
    </div>
</footer>
