<?php
/**
 * SazehShop — نقطه ورود برنامه (Front Controller)
 * همه درخواست‌ها (به جز فایل‌های استاتیک) از اینجا عبور می‌کنند.
 */

declare(strict_types=1);

define('SHOPSTORE_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/bootstrap.php';

$route = route_resolve(request_path(), request_method());

if ($route === null) {
    render_error(404);
    return;
}

[$file, $handler, $params] = $route;

require BASE_PATH . '/app/controllers/' . $file;

try {
    $handler(...array_values($params));
} catch (Throwable $e) {
    app_log('controller error: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    render_error(is_debug() ? 500 : 500, is_debug() ? $e : null);
}
