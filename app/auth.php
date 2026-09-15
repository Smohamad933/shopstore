<?php
/**
 * احراز هویت و دسترسی کاربران
 */

declare(strict_types=1);

function current_user(): ?array
{
    static $user = null;
    static $loaded = false;

    if ($loaded) {
        return $user;
    }
    $loaded = true;

    $id = (int) ($_SESSION['user_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }

    $user = db_one('SELECT * FROM users WHERE id = :id AND is_active = 1', ['id' => $id]);
    if ($user === null) {
        unset($_SESSION['user_id']);
    }

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

function require_login(string $redirectTo = '/account'): void
{
    if (!is_logged_in()) {
        $_SESSION['intended'] = request_path();
        flash_error('برای ادامه ابتدا وارد حساب کاربری خود شوید.');
        redirect('/login');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        $_SESSION['intended'] = request_path();
        redirect('/admin/login');
    }
}

/** تلاش برای ورود؛ آرایه‌ای با ok/message برمی‌گرداند */
function attempt_login(string $email, string $password): array
{
    $email = mb_strtolower(trim($email));

    // محدودیت ساده روی تعداد تلاش‌ها برای جلوگیری از حمله دیکشنری
    $attempts = (int) ($_SESSION['login_attempts'] ?? 0);
    $lastAttempt = (int) ($_SESSION['login_last'] ?? 0);
    if ($attempts >= 8 && (time() - $lastAttempt) < 600) {
        return ['ok' => false, 'message' => 'تلاش‌های ناموفق زیاد بوده است. ۱۰ دقیقه بعد دوباره تلاش کنید.'];
    }

    $user = db_one('SELECT * FROM users WHERE email = :email', ['email' => $email]);

    if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
        $_SESSION['login_attempts'] = $attempts + 1;
        $_SESSION['login_last'] = time();
        return ['ok' => false, 'message' => 'ایمیل یا رمز عبور نادرست است.'];
    }

    if ((int) $user['is_active'] !== 1) {
        return ['ok' => false, 'message' => 'حساب کاربری شما غیرفعال است.'];
    }

    unset($_SESSION['login_attempts'], $_SESSION['login_last']);
    login_user($user);

    return ['ok' => true, 'user' => $user];
}

/** ورود کاربر (تغییر شناسه نشست برای جلوگیری از Session Fixation) */
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    unset($_SESSION['csrf_token']);

    // انتقال سبد خرید مهمان به حساب کاربری (فقط آمار خرید، آیتم‌ها در نشست می‌مانند)
    db_run('UPDATE users SET last_login_at = :now WHERE id = :id', [
        'now' => date('Y-m-d H:i:s'),
        'id'  => (int) $user['id'],
    ]);
}

function logout_user(): void
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

/** ثبت‌نام کاربر جدید */
function register_user(string $name, string $email, string $phone, string $password): array
{
    $email = mb_strtolower(trim($email));

    if (mb_strlen($name) < 3) {
        return ['ok' => false, 'message' => 'نام و نام خانوادگی را کامل وارد کنید.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'ایمیل وارد شده معتبر نیست.'];
    }
    if (preg_match('/^09\d{9}$/', en_digits($phone)) !== 1) {
        return ['ok' => false, 'message' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.'];
    }
    if (mb_strlen($password) < 6) {
        return ['ok' => false, 'message' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.'];
    }
    if (db_value('SELECT COUNT(*) FROM users WHERE email = :email', ['email' => $email]) > 0) {
        return ['ok' => false, 'message' => 'این ایمیل قبلاً ثبت شده است. وارد شوید یا رمز را بازیابی کنید.'];
    }

    $id = db_insert('users', [
        'name'          => clean($name, 120),
        'email'         => $email,
        'phone'         => en_digits($phone),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role'          => 'customer',
        'is_active'     => 1,
        'created_at'    => date('Y-m-d H:i:s'),
    ]);

    login_user(['id' => $id]);

    return ['ok' => true, 'id' => $id];
}

/** تبدیل کاربر مهمان به کاربر ثبت‌شده بعد از ثبت سفارش */
function ensure_user_for_order(string $name, string $phone, string $email, ?string $address = null): ?int
{
    $user = current_user();
    if ($user !== null) {
        return (int) $user['id'];
    }

    $email = mb_strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }

    $existing = db_one('SELECT id FROM users WHERE email = :email', ['email' => $email]);
    if ($existing !== null) {
        return (int) $existing['id'];
    }

    return db_insert('users', [
        'name'          => clean($name, 120),
        'email'         => $email,
        'phone'         => en_digits($phone),
        'password_hash' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
        'role'          => 'customer',
        'is_active'     => 1,
        'created_at'    => date('Y-m-d H:i:s'),
    ]);
}
