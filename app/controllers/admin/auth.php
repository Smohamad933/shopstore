<?php
/** ورود و خروج مدیران */

declare(strict_types=1);

function login_form(): void
{
    if (is_admin()) {
        redirect('/admin');
    }

    render('admin/login', [], [
        'title' => 'ورود به پنل مدیریت',
    ]);
}

function login(): void
{
    $result = attempt_login(clean(input('email', ''), 120), (string) input('password', ''));

    if (!$result['ok']) {
        flash_error($result['message']);
        redirect('/admin/login');
    }

    if (($result['user']['role'] ?? '') !== 'admin') {
        logout_user();
        flash_error('این حساب دسترسی مدیریت ندارد.');
        redirect('/admin/login');
    }

    flash_success('به پنل مدیریت خوش آمدید.');
    redirect('/admin');
}

function logout(): void
{
    logout_user();
    flash_success('از پنل مدیریت خارج شدید.');
    redirect('/admin/login');
}
