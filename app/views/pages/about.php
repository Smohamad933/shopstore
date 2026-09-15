<?php
/**
 * درباره ما
 * @var array $brands
 * @var array $stats
 */
?>
<div class="container">
    <?php partial('breadcrumb', ['items' => [['label' => 'درباره ما', 'url' => null]]]); ?>

    <div class="surface pad-lg mb-2">
        <h1>درباره <?= e(setting('site_name')) ?></h1>
        <div class="prose" style="max-width:760px;">
            <p><?= nl2br(e(setting('about_text'))) ?></p>
        </div>

        <div class="grid" style="grid-template-columns: repeat(4, minmax(0,1fr)); gap:12px; margin-top:22px;">
            <div class="surface pad center">
                <div class="strong" style="font-size:1.4rem;"><?= fa_digits($stats['products']) ?></div>
                <div class="tiny muted">کالای فعال</div>
            </div>
            <div class="surface pad center">
                <div class="strong" style="font-size:1.4rem;"><?= fa_digits($stats['brands']) ?></div>
                <div class="tiny muted">برند معتبر</div>
            </div>
            <div class="surface pad center">
                <div class="strong" style="font-size:1.4rem;"><?= fa_digits($stats['customers']) ?></div>
                <div class="tiny muted">مشتری ثبت‌شده</div>
            </div>
            <div class="surface pad center">
                <div class="strong" style="font-size:1.4rem;"><?= fa_digits($stats['orders']) ?></div>
                <div class="tiny muted">سفارش پردازش‌شده</div>
            </div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(3, minmax(0,1fr)); gap:12px; margin-bottom:16px;">
        <div class="surface pad-lg">
            <h3 style="font-size:1rem;">چرا ما؟</h3>
            <ul class="summary-list mb-0">
                <li>قیمت شفاف و به‌روز، بدون هزینه پنهان</li>
                <li>مشاوره فنی برای انتخاب صحیح تجهیزات</li>
                <li>فاکتور رسمی و گارانتی شرکتی همه کالاها</li>
                <li>تأمین عمده برای پروژه‌های ساختمانی</li>
            </ul>
        </div>
        <div class="surface pad-lg">
            <h3 style="font-size:1rem;">خدمات ما</h3>
            <ul class="summary-list mb-0">
                <li>ارسال سریع به سراسر ایران</li>
                <li>بسته‌بندی ایمن تجهیزات حساس</li>
                <li>پشتیبانی پس از فروش و معرفی نصاب</li>
                <li>استعلام قیمت پروژه‌ای در کمتر از ۲۴ ساعت</li>
            </ul>
        </div>
        <div class="surface pad-lg">
            <h3 style="font-size:1rem;">تماس مستقیم</h3>
            <div class="small">
                <div class="row mb-1"><?= icon('phone') ?> <a href="tel:<?= e(en_digits(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></div>
                <div class="row mb-1"><?= icon('mail') ?> <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div>
                <div class="row mb-1"><?= icon('pin') ?> <?= e(setting('address')) ?></div>
                <div class="row"><?= icon('clock') ?> <?= e(setting('work_hours')) ?></div>
            </div>
            <a class="btn btn-primary btn-block mt-2" href="<?= url('/contact') ?>">ارسال پیام</a>
        </div>
    </div>

    <?php if ($brands !== []): ?>
        <div class="surface pad-lg">
            <h3 style="font-size:1rem;">برندهایی که با آن‌ها کار می‌کنیم</h3>
            <div class="brand-strip mt-2">
                <?php foreach ($brands as $brand): ?>
                    <span class="brand-pill"><?= e($brand['brand']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
