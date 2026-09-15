<?php /** ثبت‌نام */ ?>
<div class="container">
    <div class="auth-card">
        <h1>ساخت حساب کاربری</h1>
        <p class="sub">با یک حساب کاربری، سفارش‌ها و آدرس‌هایتان ذخیره می‌شود.</p>

        <form method="post" action="<?= url('/register') ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label class="label" for="name">نام و نام خانوادگی</label>
                <input class="input" id="name" type="text" name="name" required value="<?= e(old('name')) ?>">
            </div>
            <div class="field">
                <label class="label" for="email">ایمیل</label>
                <input class="input" id="email" type="email" name="email" required value="<?= e(old('email')) ?>">
            </div>
            <div class="field">
                <label class="label" for="phone">شماره موبایل</label>
                <input class="input" id="phone" type="tel" name="phone" required inputmode="numeric"
                       placeholder="09xxxxxxxxx" value="<?= e(old('phone')) ?>">
            </div>
            <div class="field">
                <label class="label" for="password">رمز عبور</label>
                <input class="input" id="password" type="password" name="password" required minlength="6">
                <div class="hint">حداقل ۶ کاراکتر شامل حرف و رقم</div>
            </div>
            <button class="btn btn-primary btn-block btn-lg" type="submit">ثبت‌نام</button>
        </form>

        <p class="small center mt-2 mb-0">
            قبلاً ثبت‌نام کرده‌اید؟ <a href="<?= url('/login') ?>" style="color:var(--brand); font-weight:600;">وارد شوید</a>
        </p>
    </div>
</div>
