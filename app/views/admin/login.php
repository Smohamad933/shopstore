<?php /** ورود مدیران */ ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود به پنل مدیریت | <?= e(setting('site_name')) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('/assets/css/admin.css') ?>">
</head>
<body>
<div class="admin-login">
    <div class="card">
        <div class="card-body">
            <div class="brand">
                <span class="logo-mark"><?= mb_substr(setting('site_name'), 0, 1) ?></span>
                <h1 style="font-size:1.15rem; margin-bottom:2px;">پنل مدیریت <?= e(setting('site_name')) ?></h1>
                <p class="muted small">برای ادامه، اطلاعات حساب مدیر را وارد کنید.</p>
            </div>

            <?php foreach (flash_pull() as $item): ?>
                <div class="alert alert-<?= e($item['type'] === 'success' ? 'success' : 'error') ?>">
                    <span><?= e($item['message']) ?></span>
                </div>
            <?php endforeach; ?>

            <form method="post" action="<?= url('/admin/login') ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label class="label" for="email">ایمیل مدیر</label>
                    <input class="input" id="email" type="email" name="email" required autofocus placeholder="admin@sazehshop.ir">
                </div>
                <div class="field">
                    <label class="label" for="password">رمز عبور</label>
                    <input class="input" id="password" type="password" name="password" required>
                </div>
                <button class="btn btn-primary btn-block btn-lg" type="submit">ورود به پنل</button>
            </form>

            <div class="alert alert-info mt-2 mb-0">
                <span>حساب نمونه مدیر: <b>admin@sazehshop.ir</b> / رمز: <b>admin1234</b></span>
            </div>

            <p class="center small mt-2 mb-0"><a href="<?= url('/') ?>">بازگشت به سایت</a></p>
        </div>
    </div>
</div>
</body>
</html>
