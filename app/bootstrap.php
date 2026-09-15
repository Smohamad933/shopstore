<?php
/**
 * راه‌انداز برنامه
 * ---------------------------------------------------------------------------
 * ترتیب بارگذاری: تنظیمات → توابع کمکی → دیتابیس → منطق دامنه → روتر → ویوها
 */

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require BASE_PATH . '/app/helpers.php';
require BASE_PATH . '/app/icons.php';

date_default_timezone_set((string) config('timezone', 'Asia/Tehran'));
mb_internal_encoding('UTF-8');

// --- خطاها: در حالت debug نمایش داده می‌شوند، در غیر این صورت فقط لاگ ---
$debug = is_debug();
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

set_error_handler(function (int $severity, string $message, string $file = '', int $line = 0): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    app_log(sprintf('PHP error [%d]: %s in %s:%d', $severity, $message, $file, $line));
    if (is_debug() && in_array($severity, [E_ERROR, E_WARNING, E_USER_ERROR, E_USER_WARNING], true)) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return true;
});

require BASE_PATH . '/app/db.php';
require BASE_PATH . '/app/csrf.php';
require BASE_PATH . '/app/flash.php';
require BASE_PATH . '/app/auth.php';
require BASE_PATH . '/app/repo.php';
require BASE_PATH . '/app/cart.php';
require BASE_PATH . '/app/router.php';
require BASE_PATH . '/app/views.php';

// --- نشست‌ها: در پوشه storage ذخیره می‌شوند تا با ری‌استارت هم باقی بمانند ---
$sessionDir = BASE_PATH . '/storage/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0775, true);
}
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    session_save_path($sessionDir);
}
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30,
    'path'     => base_path_url() === '' ? '/' : base_path_url() . '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (($_SERVER['HTTPS'] ?? '') === 'on'),
]);
session_name('SAZEHSHOP_SESSION');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// --- هدرهای امنیتی پایه (هدر iframe عمداً اینجا ست نمی‌شود) ---
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header_remove('X-Powered-By');
}

// --- اتصال دیتابیس (بار اول، خودکار نصب می‌شود) ---
try {
    db();
} catch (Throwable $e) {
    app_log('bootstrap db error: ' . $e->getMessage());
    if (!is_debug()) {
        http_response_code(500);
        exit('خطای سرور. لطفاً بعداً تلاش کنید.');
    }
    throw $e;
}

// --- اعتبارسنجی CSRF برای همه درخواست‌های POST ---
if (is_post() && !csrf_check()) {
    if (is_ajax()) {
        json_response(['ok' => false, 'message' => 'توکن امنیتی نامعتبر است. صفحه را رفرش کنید.'], 419);
    }
    http_response_code(419);
    render_error(419);
    exit;
}
