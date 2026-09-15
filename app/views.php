<?php
/**
 * موتور قالب‌ها (PHP خالص، بدون موتور خارجی)
 */

declare(strict_types=1);

/** رندر یک فایل ویو و برگرداندن خروجی به صورت رشته */
function view(string $name, array $data = []): string
{
    $file = BASE_PATH . '/app/views/' . str_replace('..', '', $name) . '.php';
    if (!is_file($file)) {
        throw new RuntimeException('view not found: ' . $name);
    }

    extract($data, EXTR_SKIP);
    ob_start();
    require $file;
    return (string) ob_get_clean();
}

/** رندر ویو داخل قالب اصلی سایت */
function render(string $name, array $data = [], array $options = []): void
{
    $meta = array_merge([
        'title'       => $options['title'] ?? null,
        'description' => $options['description'] ?? setting('site_description'),
        'canonical'   => $options['canonical'] ?? absolute_url(request_path()),
        'og_image'    => $options['og_image'] ?? static_url('/assets/img/hero-1.jpg'),
        'body_class'  => $options['body_class'] ?? '',
        'no_index'    => $options['no_index'] ?? false,
    ], $options);

    $shared = shared_view_data();
    $shared['meta'] = $meta;

    // ویو محتوا + داده‌های مشترک (کاربر جاری، دسته‌بندی‌ها، تعداد سبد خرید…)
    $content = view($name, $data + $shared);

    $data['content'] = $content;
    extract($data + $shared, EXTR_OVERWRITE);
    require BASE_PATH . '/app/views/layout.php';
}

/** رندر ویو داخل قالب پنل مدیریت */
function render_admin(string $name, array $data = [], array $options = []): void
{
    $meta = array_merge([
        'title'      => $options['title'] ?? 'پنل مدیریت',
        'body_class' => $options['body_class'] ?? '',
    ], $options);

    $shared = [
        'meta'        => $meta,
        'adminUser'   => current_user(),
        'flashItems'  => flash_pull(),
        'orderStatus' => ORDER_STATUSES,
        'settingsAll' => settings(),
    ];

    $content = view($name, $data + $shared);

    $data['content'] = $content;
    extract($data + $shared, EXTR_OVERWRITE);
    require BASE_PATH . '/app/views/admin/layout.php';
}

/** داده‌های مشترک همه صفحات */
function shared_view_data(): array
{
    static $shared = null;
    if ($shared !== null) {
        return $shared;
    }

    $shared = [
        'siteName'      => setting('site_name'),
        'settingsAll'   => settings(),
        'navCategories' => nav_categories(),
        'cartCount'     => cart_count(),
        'user'          => current_user(),
        'flashItems'    => flash_pull(),
        'meta'          => [],
    ];

    return $shared;
}

/** نمایش خطا با کد HTTP مناسب */
function render_error(int $code, ?Throwable $error = null): void
{
    http_response_code($code);
    $titles = [
        404 => 'صفحه‌ای که دنبالش بودید پیدا نشد',
        419 => 'نشست شما منقضی شده است',
        500 => 'خطایی در سرور رخ داد',
    ];

    if (is_ajax()) {
        json_response([
            'ok'      => false,
            'code'    => $code,
            'message' => $titles[$code] ?? 'خطای ناشناخته',
            'debug'   => is_debug() && $error ? $error->getMessage() : null,
        ], $code);
    }

    render('errors/error', [
        'code'      => $code,
        'title'     => $titles[$code] ?? 'خطا',
        'errorInfo' => is_debug() && $error ? $error->getMessage() . ' — ' . $error->getFile() . ':' . $error->getLine() : null,
    ], ['title' => ($titles[$code] ?? 'خطا') . ' | ' . setting('site_name'), 'no_index' => true]);
}

/** چاپ یک بخش قابل استفاده مجدد */
function partial(string $name, array $data = []): void
{
    echo view('partials/' . $name, $data);
}

/** ساخت آدرس صفحه‌بندی با حفظ پارامترهای فیلتر */
function page_url(string $basePath, array $query, int $page): string
{
    $query['page'] = $page;
    return url($basePath, array_filter($query, fn ($v) => $v !== null && $v !== '' && $v !== []));
}
