<?php
/**
 * توابع کمکی عمومی (بدون وابستگی به دیتابیس)
 */

declare(strict_types=1);

/** خواندن تنظیمات پروژه از فایل app/config.php */
function config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require BASE_PATH . '/app/config.php';
    }
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? $default;
}

/** آیا حالت اشکال‌زدایی فعال است؟ */
function is_debug(): bool
{
    return (bool) config('debug', false);
}

/**
 * مسیر پایه برنامه (برای نصب روی زیرپوشه هم کار می‌کند)
 * اگر DocumentRoot روی public باشد، مقدار خالی برمی‌گردد.
 * اگر پروژه در زیرپوشه باشد (مثلاً /shop/public) مقدار /shop/public برمی‌گردد.
 */
function base_path_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    $base = ($dir === '/' || $dir === '.') ? '' : $dir;

    // حالت زیرپوشه با ریدایرکت به public/ (فایل .htaccess ریشه)
    $forwarded = forwarded_uri();
    if ($base === '' && preg_match('#^/public/#', $forwarded)) {
        $base = '/public';
    }

    return $base;
}

/** URI اصلی درخواست (با احتساب پروکسی/dev-server) */
function forwarded_uri(): string
{
    static $uri = null;
    if ($uri !== null) {
        return $uri;
    }
    $candidate = (string) ($_SERVER['HTTP_X_FORWARDED_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/');
    $uri = $candidate === '' ? '/' : $candidate;
    return $uri;
}

/** مسیر تمیز درخواست (بدون query string و بدون مسیر پایه) */
function request_path(): string
{
    static $path = null;
    if ($path !== null) {
        return $path;
    }

    $uri = explode('?', forwarded_uri(), 2)[0];
    $uri = rawurldecode($uri);
    $base = base_path_url();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    $uri = '/' . trim(preg_replace('#/+#', '/', $uri) ?? '/', '/');

    $path = $uri === '//' ? '/' : $uri;
    return $path;
}

function request_method(): string
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
}

/** ساخت URL با مسیر پایه و کاراکترهای امن */
function url(string $path = '/', array $query = []): string
{
    $path = '/' . ltrim($path, '/');
    if ($path !== '/' && substr($path, -1) === '/') {
        $path = rtrim($path, '/');
    }
    $full = base_path_url() . ($path === '/' ? '/' : $path);
    if ($query !== []) {
        $full .= '?' . http_build_query($query);
    }
    return $full;
}

/** URL کامل و مطلق (برای og:url، sitemap، بازگشت از درگاه پرداخت) */
function absolute_url(string $path = '/', array $query = []): string
{
    $scheme = 'http';
    if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || ($_SERVER['HTTPS'] ?? '') === 'on') {
        $scheme = 'https';
    }
    $host = (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost');
    return $scheme . '://' . $host . url($path, $query);
}

/** آدرس فایل‌های استاتیک با نسخه‌بندی (برای شکستن کش مرورگر) */
function asset(string $path): string
{
    static $version = null;
    if ($version === null) {
        $version = (string) (setting('asset_version') ?: '1');
    }
    return url($path) . (str_contains($path, '?') ? '&' : '?') . 'v=' . $version;
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query), true, 302);
    exit;
}

/** خروجی امن در HTML */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** پاک‌سازی ورودی متنی */
function clean(mixed $value, int $maxLength = 5000): string
{
    $value = is_scalar($value) ? (string) $value : '';
    $value = str_replace(["\0"], '', $value);
    $value = trim(preg_replace('/[ \t]+/u', ' ', $value) ?? '');
    return mb_substr($value, 0, $maxLength);
}

/* -------------------------------------------------------------------------
 * اعداد و قیمت‌ها (نمایش فارسی)
 * ---------------------------------------------------------------------- */

/** تبدیل ارقام لاتین به فارسی */
function fa_digits(string|int|float|null $value): string
{
    return strtr((string) $value, [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ]);
}

/** تبدیل ارقام فارسی/عربی به لاتین (برای ورودی فرم‌ها) */
function en_digits(?string $value): string
{
    return strtr((string) $value, [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5',
        '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5',
        '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '٬' => '', ',' => '', ' ' => '',
    ]);
}

/** نمایش عدد با جداکننده هزارگان و ارقام فارسی: ۱۶٬۰۹۶٬۰۰۰ */
function money(int|float|string|null $amount, bool $free = false): string
{
    $amount = (int) $amount;
    if ($free && $amount === 0) {
        return 'رایگان';
    }
    return fa_digits(number_format($amount, 0, '.', '٬'));
}

function toman(int|float|string|null $amount): string
{
    return money($amount) . ' ' . config('currency', 'تومان');
}

/** تبدیل مبلغ به حروف فارسی (برای فاکتور و صفحه پرداخت) */
function amount_in_words(int|float $number): string
{
    $number = (int) $number;
    if ($number === 0) {
        return 'صفر تومان';
    }

    $yekan = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
    $dahgan = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
    $dah = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
    $sadgan = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
    $scale = ['', ' هزار', ' میلیون', ' میلیارد'];

    $groups = [];
    while ($number > 0) {
        $groups[] = $number % 1000;
        $number = intdiv($number, 1000);
    }

    $words = [];
    foreach ($groups as $index => $group) {
        if ($group === 0) {
            continue;
        }
        $parts = [];
        $sad = intdiv($group, 100);
        $rest = $group % 100;
        if ($sad > 0) {
            $parts[] = $sadgan[$sad];
        }
        if ($rest >= 10 && $rest < 20) {
            $parts[] = $dah[$rest - 10];
        } else {
            $dahg = intdiv($rest, 10);
            $yek = $rest % 10;
            if ($dahg > 0) {
                $parts[] = $dahgan[$dahg];
            }
            if ($yek > 0) {
                $parts[] = $yekan[$yek];
            }
        }
        // «یک هزار» → «هزار»
        if ($group === 1 && $index > 0) {
            $parts = [];
        }
        $words[$index] = implode(' و ', $parts) . $scale[$index];
    }

    ksort($words);
    $result = implode(' و ', array_reverse($words, true));

    return fa_digits(trim($result)) . ' ' . config('currency', 'تومان');
}

/* -------------------------------------------------------------------------
 * تاریخ شمسی
 * ---------------------------------------------------------------------- */

/** تبدیل تاریخ میلادی به شمسی (بدون وابستگی خارجی) */
function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $gDaysInMonth = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $jy = $gy <= 1600 ? 0 : 979;
    $gy -= $gy <= 1600 ? 621 : 1600;
    $gy2 = $gm > 2 ? $gy + 1 : $gy;
    $days = (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400)
        - 80 + $gd + $gDaysInMonth[$gm - 1];
    $jy += 33 * intdiv($days, 12053);
    $days %= 12053;
    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    $jm = $days < 186 ? 1 + intdiv($days, 31) : 7 + intdiv($days - 186, 30);
    $jd = 1 + ($days < 186 ? $days % 31 : ($days - 186) % 30);

    return [$jy, $jm, $jd];
}

const JALALI_MONTHS = [
    1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
    7 => 'مهر', 8 => 'آبان', 9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
];

/** تاریخ شمسی به صورت رشته. حالت‌ها: date, date_time, long, day_month */
function jdate(?string $datetime = null, string $format = 'date'): string
{
    $timestamp = $datetime ? strtotime($datetime) : time();
    if ($timestamp === false) {
        return '';
    }
    [$jy, $jm, $jd] = gregorian_to_jalali((int) date('Y', $timestamp), (int) date('n', $timestamp), (int) date('j', $timestamp));
    $time = date('H:i', $timestamp);

    return match ($format) {
        'date_time' => fa_digits(sprintf('%04d/%02d/%02d', $jy, $jm, $jd)) . ' ساعت ' . fa_digits($time),
        'long'      => fa_digits($jd) . ' ' . JALALI_MONTHS[$jm] . ' ' . fa_digits($jy),
        'day_month' => fa_digits($jd) . ' ' . JALALI_MONTHS[$jm],
        'time'      => fa_digits($time),
        default     => fa_digits(sprintf('%04d/%02d/%02d', $jy, $jm, $jd)),
    };
}

/** زمان نسبی فارسی: «۳ روز پیش» */
function jdiff(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $diff = time() - (int) strtotime($datetime);
    if ($diff < 60) {
        return 'لحظه‌ای پیش';
    }
    foreach ([[31536000, 'سال'], [2592000, 'ماه'], [604800, 'هفته'], [86400, 'روز'], [3600, 'ساعت'], [60, 'دقیقه']] as [$seconds, $label]) {
        if ($diff >= $seconds) {
            return fa_digits(intdiv($diff, $seconds)) . ' ' . $label . ' پیش';
        }
    }
    return 'لحظه‌ای پیش';
}

/* -------------------------------------------------------------------------
 * متفرقه
 * ---------------------------------------------------------------------- */

function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('#[^\p{L}\p{N}]+#u', '-', $text) ?? '';
    $text = trim($text, '-');
    return mb_strtolower($text) ?: 'item';
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_input(): array
{
    static $data = null;
    if ($data === null) {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true) ?: [];
    }
    return is_array($data) ? $data : [];
}

function is_post(): bool
{
    return request_method() === 'POST';
}

/** خواندن ورودی از فرم (POST) یا بدنه JSON یا query string */
function input(string $key, mixed $default = null): mixed
{
    if (array_key_exists($key, $_POST)) {
        return $_POST[$key];
    }
    $json = json_input();
    if (array_key_exists($key, $json)) {
        return $json[$key];
    }
    if (array_key_exists($key, $_GET)) {
        return $_GET[$key];
    }
    return $default;
}

function int_input(string $key, int $default = 0): int
{
    $value = input($key, $default);
    if (is_bool($value)) {
        return $value ? 1 : 0;
    }
    return (int) en_digits(is_scalar($value) ? (string) $value : '0');
}

function is_ajax(): bool
{
    return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
        || str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
        || str_contains(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'application/json');
}

function app_log(string $message): void
{
    $dir = BASE_PATH . '/storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($dir . '/app-' . date('Y-m-d') . '.log', '[' . date('c') . '] ' . $message . PHP_EOL, FILE_APPEND);
}

/** آدرس تصویر محصول با پشتیبانی از تصاویر آپلودی و پیش‌فرض */
function product_image(?string $image): string
{
    if (!$image) {
        return url('/assets/img/placeholder.svg');
    }
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }
    return url('/' . ltrim($image, '/'));
}

/** یافتن دسته‌بندی بر اساس slug در فهرست داده‌شده */
function category_by_slug(array $categories, string $slug): ?array
{
    foreach ($categories as $category) {
        if ($category['slug'] === $slug) {
            return $category;
        }
    }
    return null;
}

/** فهرست استان‌های ایران (برای فرم‌های آدرس) */
function provinces(): array
{
    return [
        'آذربایجان شرقی', 'آذربایجان غربی', 'اردبیل', 'اصفهان', 'البرز', 'ایلام', 'بوشهر', 'تهران',
        'چهارمحال و بختیاری', 'خراسان جنوبی', 'خراسان رضوی', 'خراسان شمالی', 'خوزستان', 'زنجان',
        'سمنان', 'سیستان و بلوچستان', 'فارس', 'قزوین', 'قم', 'کردستان', 'کرمان', 'کرمانشاه',
        'کهگیلویه و بویراحمد', 'گلستان', 'گیلان', 'لرستان', 'مازندران', 'مرکزی', 'هرمزگان', 'همدان', 'یزد',
    ];
}

/** ساخت صفحه‌بندی */
function paginate(int $total, int $perPage, int $currentPage, string $basePath, array $query = []): array
{
    $lastPage = max(1, (int) ceil($total / max(1, $perPage)));
    $currentPage = max(1, min($currentPage, $lastPage));

    $links = [];
    for ($page = 1; $page <= $lastPage; $page++) {
        if ($lastPage > 7 && $page > 2 && $page < $lastPage - 1 && abs($page - $currentPage) > 1) {
            if (end($links) !== '…') {
                $links[] = '…';
            }
            continue;
        }
        $links[] = $page;
    }

    return [
        'total'    => $total,
        'per_page' => $perPage,
        'page'     => $currentPage,
        'last'     => $lastPage,
        'from'     => $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1,
        'to'       => min($currentPage * $perPage, $total),
        'links'    => $links,
        'url'      => fn (int $page) => url($basePath, array_filter($query + ['page' => $page], fn ($v) => $v !== null && $v !== '')) ,
    ];
}

/** درصد تخفیف */
function discount_percent(int $price, int $salePrice): int
{
    if ($price <= 0 || $salePrice <= 0 || $salePrice >= $price) {
        return 0;
    }
    return (int) round((($price - $salePrice) / $price) * 100);
}

/** ستاره‌های امتیاز */
function stars(float $rating): string
{
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $rating >= $i - 0.25 ? '★' : '☆';
    }
    return $out;
}
