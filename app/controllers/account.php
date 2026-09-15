<?php
/**
 * حساب کاربری: ورود، ثبت‌نام، سفارش‌ها، اطلاعات شخصی و آدرس‌ها
 */

declare(strict_types=1);

function login_form(): void
{
    if (is_logged_in()) {
        redirect('/account');
    }
    render('account/login', [], [
        'title'      => 'ورود به حساب کاربری',
        'no_index'   => true,
        'body_class' => 'page-auth',
    ]);
}

function login(): void
{
    $email = clean(input('email', ''), 120);
    $password = (string) input('password', '');

    $result = attempt_login($email, $password);

    if (!$result['ok']) {
        old_keep(['email' => $email]);
        flash_error($result['message']);
        redirect('/login');
    }

    old_clear();
    flash_success('خوش آمدید ' . $result['user']['name'] . '!');

    $intended = $_SESSION['intended'] ?? null;
    unset($_SESSION['intended']);

    if ($result['user']['role'] === 'admin' && ($intended === null || str_starts_with((string) $intended, '/admin'))) {
        redirect('/admin');
    }

    redirect(is_string($intended) && str_starts_with($intended, '/') ? $intended : '/account');
}

function register_form(): void
{
    if (is_logged_in()) {
        redirect('/account');
    }
    render('account/register', [], [
        'title'      => 'ساخت حساب کاربری',
        'no_index'   => true,
        'body_class' => 'page-auth',
    ]);
}

function register(): void
{
    $result = register_user(
        clean(input('name', ''), 120),
        clean(input('email', ''), 120),
        (string) input('phone', ''),
        (string) input('password', '')
    );

    if (!$result['ok']) {
        old_keep(['name' => input('name'), 'email' => input('email'), 'phone' => input('phone')]);
        flash_error($result['message']);
        redirect('/register');
    }

    old_clear();
    flash_success('حساب کاربری شما ساخته شد. خوش آمدید!');
    redirect('/account');
}

function logout(): void
{
    logout_user();
    flash_success('از حساب کاربری خارج شدید.');
    redirect('/');
}

function dashboard(): void
{
    require_login();
    $user = current_user();

    render('account/dashboard', [
        'orders'    => user_orders((int) $user['id'], 4),
        'addresses' => db_all('SELECT * FROM addresses WHERE user_id = :id ORDER BY is_default DESC', ['id' => (int) $user['id']]),
        'stats'     => [
            'orders' => (int) db_value('SELECT COUNT(*) FROM orders WHERE user_id = :id', ['id' => (int) $user['id']], 0),
            'spent'  => (int) db_value(
                "SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = :id AND status IN ('paid','processing','shipped','delivered')",
                ['id' => (int) $user['id']],
                0
            ),
            'pending' => (int) db_value(
                "SELECT COUNT(*) FROM orders WHERE user_id = :id AND status IN ('pending','paid','processing','shipped')",
                ['id' => (int) $user['id']],
                0
            ),
        ],
    ], ['title' => 'پنل کاربری', 'no_index' => true, 'body_class' => 'page-account']);
}

function orders(): void
{
    require_login();
    $user = current_user();

    render('account/orders', [
        'orders' => user_orders((int) $user['id'], 50),
    ], ['title' => 'سفارش‌های من', 'no_index' => true, 'body_class' => 'page-account']);
}

function order(string $code): void
{
    require_login();
    $user = current_user();
    $order = find_order($code);

    if ($order === null || (int) $order['user_id'] !== (int) $user['id']) {
        render_error(404);
        return;
    }

    render('order', [
        'order'       => $order,
        'isOwner'     => true,
        'showDetails' => true,
        'accountView' => true,
    ], ['title' => 'سفارش ' . $order['code'], 'no_index' => true, 'body_class' => 'page-account']);
}

function profile(): void
{
    require_login();
    render('account/profile', [], [
        'title'      => 'اطلاعات حساب',
        'no_index'   => true,
        'body_class' => 'page-account',
    ]);
}

function profile_save(): void
{
    require_login();
    $user = current_user();

    $name = clean(input('name', ''), 120);
    $phone = en_digits((string) input('phone', ''));
    $password = (string) input('password', '');

    if (mb_strlen($name) < 3) {
        flash_error('نام و نام خانوادگی را کامل وارد کنید.');
        redirect('/account/profile');
    }

    $data = ['name' => $name, 'phone' => $phone];
    if ($password !== '') {
        if (mb_strlen($password) < 6) {
            flash_error('رمز عبور جدید باید حداقل ۶ کاراکتر باشد.');
            redirect('/account/profile');
        }
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }

    db_update('users', $data, 'id = :id', ['id' => (int) $user['id']]);
    flash_success($password !== '' ? 'اطلاعات و رمز عبور بروزرسانی شد.' : 'اطلاعات حساب بروزرسانی شد.');
    redirect('/account/profile');
}

function addresses(): void
{
    require_login();
    $user = current_user();

    render('account/addresses', [
        'addresses' => db_all('SELECT * FROM addresses WHERE user_id = :id ORDER BY is_default DESC, id DESC', ['id' => (int) $user['id']]),
        'provinces' => provinces(),
    ], ['title' => 'آدرس‌های من', 'no_index' => true, 'body_class' => 'page-account']);
}

function address_save(): void
{
    require_login();
    $user = current_user();

    $receiver = clean(input('receiver', ''), 120);
    $phone = en_digits((string) input('phone', ''));
    $province = clean(input('province', ''), 60);
    $city = clean(input('city', ''), 60);
    $address = clean(input('address', ''), 400);
    $postalCode = clean(input('postal_code', ''), 20);
    $title = clean(input('title', 'آدرس من'), 60);

    if (mb_strlen($receiver) < 3 || $province === '' || $city === '' || mb_strlen($address) < 10) {
        flash_error('اطلاعات آدرس را کامل وارد کنید.');
        redirect('/account/addresses');
    }

    $data = [
        'user_id'     => (int) $user['id'],
        'title'       => $title,
        'receiver'    => $receiver,
        'phone'       => $phone,
        'province'    => $province,
        'city'        => $city,
        'address'     => $address,
        'postal_code' => $postalCode ?: null,
        'is_default'  => input('is_default') ? 1 : 0,
        'created_at'  => date('Y-m-d H:i:s'),
    ];

    if ((int) $data['is_default'] === 1) {
        db_run('UPDATE addresses SET is_default = 0 WHERE user_id = :id', ['id' => (int) $user['id']]);
    }

    db_insert('addresses', $data);
    flash_success('آدرس جدید ثبت شد.');
    redirect('/account/addresses');
}

function address_delete(string $id): void
{
    require_login();
    $user = current_user();

    db_run('DELETE FROM addresses WHERE id = :id AND user_id = :user', [
        'id'   => (int) $id,
        'user' => (int) $user['id'],
    ]);

    flash_success('آدرس حذف شد.');
    redirect('/account/addresses');
}
