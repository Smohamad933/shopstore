<?php /** ورود به حساب کاربری */ ?>
<div class="container">
    <div class="auth-card">
        <h1>ورود به حساب کاربری</h1>
        <p class="sub">برای پیگیری سفارش‌ها و خرید سریع‌تر وارد شوید.</p>

        <form method="post" action="<?= url('/login') ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label class="label" for="email">ایمیل</label>
                <input class="input" id="email" type="email" name="email" required autofocus
                       value="<?= e(old('email')) ?>" placeholder="you@example.com">
            </div>
            <div class="field">
                <label class="label" for="password">رمز عبور</label>
                <input class="input" id="password" type="password" name="password" required minlength="6">
            </div>
            <button class="btn btn-primary btn-block btn-lg" type="submit">ورود</button>
        </form>

        <p class="small center mt-2 mb-0">
            حساب کاربری ندارید؟ <a href="<?= url('/register') ?>" style="color:var(--brand); font-weight:600;">ثبت‌نام کنید</a>
        </p>

        <div class="alert alert-info mt-2 mb-0">
            <?= icon('info') ?>
            <span>
                حساب آزمایشی برای پیش‌نمایش:<br>
                کاربر: <b>demo@sazehshop.ir</b> / رمز: <b>demo1234</b><br>
                مدیر: <b>admin@sazehshop.ir</b> / رمز: <b>admin1234</b>
            </span>
        </div>
    </div>
</div>
