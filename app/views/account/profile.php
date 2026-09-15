<?php
/** ویرایش اطلاعات حساب */
?>
<div class="container">
    <h1 class="mb-2">اطلاعات حساب</h1>

    <div class="account-layout">
        <?php partial('account-nav', ['user' => $user]); ?>

        <div class="surface pad-lg">
            <h3 style="font-size:1rem;">ویرایش اطلاعات</h3>
            <form method="post" action="<?= url('/account/profile') ?>" style="max-width:520px;">
                <?= csrf_field() ?>
                <div class="field">
                    <label class="label" for="name">نام و نام خانوادگی</label>
                    <input class="input" id="name" type="text" name="name" required value="<?= e($user['name']) ?>">
                </div>
                <div class="field">
                    <label class="label" for="phone">شماره موبایل</label>
                    <input class="input" id="phone" type="tel" name="phone" inputmode="numeric" value="<?= e($user['phone']) ?>">
                </div>
                <div class="field">
                    <label class="label">ایمیل (غیرقابل تغییر)</label>
                    <input class="input" type="email" value="<?= e($user['email']) ?>" disabled>
                    <div class="hint">ایمیل حساب، شناسه ورود شما است. برای تغییر آن با پشتیبانی تماس بگیرید.</div>
                </div>
                <div class="field">
                    <label class="label" for="password">رمز عبور جدید (اختیاری)</label>
                    <input class="input" id="password" type="password" name="password" minlength="6" placeholder="خالی بگذارید تا تغییر نکند">
                </div>

                <div class="row">
                    <button class="btn btn-primary" type="submit">ذخیره تغییرات</button>
                    <a class="btn btn-ghost" href="<?= url('/account') ?>">انصراف</a>
                </div>
            </form>

            <div class="mt-3" style="border-top:1px solid var(--line-2); padding-top:14px;">
                <div class="row-between">
                    <div>
                        <b class="small">عضویت از</b>
                        <div class="tiny muted"><?= e(jdate($user['created_at'], 'long')) ?></div>
                    </div>
                    <div>
                        <b class="small">آخرین ورود</b>
                        <div class="tiny muted"><?= $user['last_login_at'] ? e(jdate($user['last_login_at'], 'date_time')) : '—' ?></div>
                    </div>
                    <a class="btn btn-sm btn-danger" href="<?= url('/logout') ?>">خروج از حساب</a>
                </div>
            </div>
        </div>
    </div>
</div>
