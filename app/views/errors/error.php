<?php
/** صفحه خطا (۴۰۴ / ۵۰۰ / ۴۱۹) */
?>
<div class="container">
    <div class="empty-state" style="margin-block: 40px;">
        <?= icon('info') ?>
        <h1 style="font-size:3rem; margin-bottom:0;"><?= fa_digits($code) ?></h1>
        <h3><?= e($title) ?></h3>
        <p class="muted small"><?= e($code === 404 ? 'ممکن است آدرس تغییر کرده یا محصول حذف شده باشد. می‌توانید از جست‌وجو یا دسته‌بندی‌ها استفاده کنید.' : 'لطفاً چند لحظه بعد دوباره تلاش کنید. اگر مشکل ادامه داشت با پشتیبانی تماس بگیرید.') ?></p>

        <?php if (!empty($errorInfo)): ?>
            <div class="alert alert-error" style="text-align:start; max-width:640px; margin-inline:auto;">
                <div class="small" style="font-family:monospace; direction:ltr; text-align:left;"><?= e($errorInfo) ?></div>
            </div>
        <?php endif; ?>

        <div class="row" style="justify-content:center; flex-wrap:wrap; margin-top:10px;">
            <a class="btn btn-primary" href="<?= url('/') ?>">بازگشت به صفحه اصلی</a>
            <a class="btn" href="<?= url('/search') ?>">جست‌وجوی محصولات</a>
            <a class="btn btn-ghost" href="<?= url('/contact') ?>">تماس با پشتیبانی</a>
        </div>
    </div>
</div>
