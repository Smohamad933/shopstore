<?php /** صفحه آفلاین (نسخه داخل سایت) */ ?>
<div class="container container-narrow">
    <div class="empty-state" style="margin-block: 36px;">
        <?= icon('wifi-off') ?>
        <h1 style="font-size:1.5rem;">اتصال اینترنت قطع است</h1>
        <p class="muted small">
            صفحاتی که قبلاً بازدید کرده‌اید از حافظه دستگاه نمایش داده می‌شوند. برای نهایی کردن سفارش
            یا مشاهده قیمت‌های جدید، به اینترنت نیاز دارید.
        </p>
        <div class="row" style="justify-content:center; flex-wrap:wrap;">
            <a class="btn btn-primary" href="<?= url('/') ?>">تلاش مجدد</a>
            <a class="btn btn-ghost" href="<?= url('/cart') ?>">سبد خرید ذخیره‌شده</a>
        </div>
    </div>
</div>
