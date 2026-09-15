<?php
/**
 * لایه دیتابیس — SQLite با PDO
 * ---------------------------------------------------------------------------
 * چرا SQLite؟ چون پروژه بدون هیچ تنظیمات اضافه‌ای (ساخت دیتابیس، یوزر/پسورد)
 * روی هر هاستی بالا می‌آید و برای حجم یک فروشگاه کوچک/متوسط کاملاً کافی است.
 * اگر بعداً خواستید به MySQL مهاجرت کنید، فقط همین فایل و schema را عوض می‌کنید.
 */

declare(strict_types=1);

/** اندازه فایل دیتابیس با پاک‌سازی کش آماری (۰ اگر وجود نداشته باشد) */
function db_file_size(string $file): int
{
    clearstatcache(true, $file);
    $size = @filesize($file);
    return $size === false ? 0 : (int) $size;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $file = (string) config('db_file');
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    // نکته: clearstatcache لازم است، چون PHP نتیجه file_exists را در حافظه نگه می‌دارد
    // و در سرورهای بلندمدت (php-fpm/php-wasm) ممکن است بعد از ساخت دیتابیس دچار اشتباه شود.
    $isNew = db_file_size($file) < 512;

    if ($isNew && !config('auto_install', true)) {
        http_response_code(500);
        exit('دیتابیس یافت نشد. ابتدا فایل database/schema.sql را اجرا کنید.');
    }

    try {
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        app_log('db connection failed: ' . $e->getMessage());
        http_response_code(500);
        exit('اتصال به دیتابیس برقرار نشد. دسترسی نوشتن پوشه storage را بررسی کنید.');
    }

    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');

    // اگر فایل دیتابیس وجود دارد ولی جدول‌ها ساخته نشده‌اند (نصب نیمه‌کاره)، نصب تکرار می‌شود
    if (!$isNew && !db_is_installed($pdo)) {
        $isNew = true;
    }

    if ($isNew && config('auto_install', true)) {
        db_install($pdo);
    }

    return $pdo;
}

/** آیا جدول‌های اصلی ساخته شده‌اند؟ */
function db_is_installed(PDO $pdo): bool
{
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name IN ('products', 'orders', 'settings')")
            ->fetchColumn();
        return (int) $count === 3;
    } catch (Throwable $e) {
        return false;
    }
}

/** اجرای schema + داده‌های نمونه (فقط بار اول) */
function db_install(PDO $pdo): void
{
    $schema = file_get_contents(BASE_PATH . '/database/schema.sql');
    if ($schema === false) {
        throw new RuntimeException('database/schema.sql یافت نشد');
    }
    $pdo->exec($schema);

    $seeder = BASE_PATH . '/database/seed.php';
    if (is_file($seeder)) {
        (require $seeder)($pdo);
    }
}

/** اجرای کوئری با پارامترهای امن */
function db_run(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_all(string $sql, array $params = []): array
{
    return db_run($sql, $params)->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $row = db_run($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_value(string $sql, array $params = [], mixed $default = null): mixed
{
    $value = db_run($sql, $params)->fetchColumn();
    return $value === false ? $default : $value;
}

function db_insert(string $table, array $data): int
{
    $columns = array_keys($data);
    $placeholders = array_map(fn ($c) => ':' . $c, $columns);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $columns),
        implode(', ', $placeholders)
    );
    db_run($sql, $data);
    return (int) db()->lastInsertId();
}

function db_update(string $table, array $data, string $where, array $whereParams = []): int
{
    $sets = implode(', ', array_map(fn ($c) => $c . ' = :' . $c, array_keys($data)));
    $stmt = db_run("UPDATE {$table} SET {$sets} WHERE {$where}", $data + $whereParams);
    return $stmt->rowCount();
}

/* -------------------------------------------------------------------------
 * تنظیمات سایت (کلید/مقدار) با کش در حافظه
 * ---------------------------------------------------------------------- */

function settings(bool $reload = false): array
{
    static $settings = null;
    if ($settings !== null && !$reload) {
        return $settings;
    }

    $stored = [];
    try {
        foreach (db_all('SELECT key, value FROM settings') as $row) {
            $stored[$row['key']] = $row['value'];
        }
    } catch (Throwable $e) {
        app_log('settings load failed: ' . $e->getMessage());
    }

    $settings = array_merge((array) config('settings_defaults', []), $stored);
    return $settings;
}

function setting(string $key, mixed $default = ''): string
{
    $settings = settings();
    $value = $settings[$key] ?? $default;
    return is_scalar($value) ? (string) $value : (string) $default;
}

function setting_set(string $key, string $value): void
{
    db_run(
        'INSERT INTO settings (key, value) VALUES (:key, :value)
         ON CONFLICT(key) DO UPDATE SET value = excluded.value',
        ['key' => $key, 'value' => $value]
    );
    settings(true);
}
