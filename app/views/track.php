<?php
/** فرم پیگیری سفارش */
?>
<div class="container container-narrow">
    <div class="auth-card" style="max-width:520px;">
        <h1>پیگیری سفارش</h1>
        <p class="sub">کد سفارش (مثل SZ-140405-123456) یا شماره موبایل خود را وارد کنید.</p>

        <form method="post" action="<?= url('/track') ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label class="label" for="code">کد سفارش یا شماره موبایل</label>
                <input class="input" id="code" type="text" name="code" required autofocus
                       placeholder="SZ-050615-A1B2C3" value="<?= e($code ?? '') ?>">
            </div>
            <button class="btn btn-primary btn-block" type="submit">پیگیری سفارش</button>
        </form>

        <div class="alert alert-info mt-2 mb-0">
            <?= icon('info') ?>
            <span>کد سفارش در پیام تأیید و در پنل کاربری شما («سفارش‌های من») موجود است.</span>
        </div>
    </div>
</div>
