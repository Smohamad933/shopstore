<?php
/**
 * روتر ساده و بدون وابستگی
 * الگوی مسیرها: /product/{slug} — پارامترها با نام به کنترلر پاس داده می‌شوند.
 */

declare(strict_types=1);

function routes(): array
{
    return [
        // ---------------------------------------------------------------- فروشگاه
        ['GET',  '/',                              'shop.php',            'home'],
        ['GET',  '/search',                        'shop.php',            'search'],
        ['GET',  '/discounts',                     'shop.php',            'discounts'],
        ['GET',  '/sitemap.xml',                   'shop.php',            'sitemap'],
        ['GET',  '/api/search',                    'shop.php',            'api_search'],
        ['GET',  '/category/{slug}',               'category.php',        'show'],
        ['GET',  '/product/{slug}',                'product.php',         'show'],
        ['POST', '/product/{slug}/review',         'product.php',         'review'],

        // ---------------------------------------------------------------- سبد خرید
        ['GET',  '/cart',                          'cart.php',            'index'],
        ['POST', '/cart/add',                      'cart.php',            'add'],
        ['POST', '/cart/update',                   'cart.php',            'update'],
        ['POST', '/cart/remove',                   'cart.php',            'remove'],
        ['POST', '/cart/coupon',                   'cart.php',            'coupon'],
        ['GET',  '/api/cart',                      'cart.php',            'api_summary'],

        // ---------------------------------------------------------------- پرداخت و سفارش
        ['GET',  '/checkout',                      'checkout.php',        'index'],
        ['POST', '/checkout',                      'checkout.php',        'store'],
        ['GET',  '/payment/{code}',                'checkout.php',        'payment'],
        ['POST', '/payment/{code}',                'checkout.php',        'payment_post'],
        ['GET',  '/checkout/verify',               'checkout.php',        'verify'],
        ['GET',  '/order/{code}',                  'checkout.php',        'show'],
        ['GET',  '/track',                         'checkout.php',        'track'],
        ['POST', '/track',                         'checkout.php',        'track_post'],

        // ---------------------------------------------------------------- حساب کاربری
        ['GET',  '/login',                         'account.php',         'login_form'],
        ['POST', '/login',                         'account.php',         'login'],
        ['GET',  '/register',                      'account.php',         'register_form'],
        ['POST', '/register',                      'account.php',         'register'],
        ['GET',  '/logout',                        'account.php',         'logout'],
        ['GET',  '/account',                       'account.php',         'dashboard'],
        ['GET',  '/account/orders',                'account.php',         'orders'],
        ['GET',  '/account/orders/{code}',         'account.php',         'order'],
        ['GET',  '/account/profile',               'account.php',         'profile'],
        ['POST', '/account/profile',               'account.php',         'profile_save'],
        ['GET',  '/account/addresses',             'account.php',         'addresses'],
        ['POST', '/account/addresses',             'account.php',         'address_save'],
        ['POST', '/account/addresses/{id}/delete', 'account.php',         'address_delete'],

        // ---------------------------------------------------------------- صفحات ثابت
        ['GET',  '/about',                         'page.php',            'about'],
        ['GET',  '/contact',                       'page.php',            'contact'],
        ['POST', '/contact',                       'page.php',            'contact_send'],
        ['GET',  '/faq',                           'page.php',            'faq'],
        ['GET',  '/terms',                         'page.php',            'terms'],
        ['GET',  '/offline',                       'page.php',            'offline'],

        // ---------------------------------------------------------------- پنل مدیریت
        ['GET',  '/admin',                         'admin/dashboard.php', 'index'],
        ['GET',  '/admin/login',                   'admin/auth.php',      'login_form'],
        ['POST', '/admin/login',                   'admin/auth.php',      'login'],
        ['GET',  '/admin/logout',                  'admin/auth.php',      'logout'],
        ['GET',  '/admin/products',                'admin/products.php',  'index'],
        ['GET',  '/admin/products/new',            'admin/products.php',  'form'],
        ['GET',  '/admin/products/{id}/edit',      'admin/products.php',  'form'],
        ['POST', '/admin/products/save',           'admin/products.php',  'save'],
        ['POST', '/admin/products/{id}/delete',    'admin/products.php',  'delete'],
        ['GET',  '/admin/categories',              'admin/categories.php','index'],
        ['POST', '/admin/categories/save',         'admin/categories.php','save'],
        ['POST', '/admin/categories/{id}/delete',  'admin/categories.php','delete'],
        ['GET',  '/admin/orders',                  'admin/orders.php',    'index'],
        ['GET',  '/admin/orders/{id}',             'admin/orders.php',    'show'],
        ['POST', '/admin/orders/{id}/status',      'admin/orders.php',    'status'],
        ['GET',  '/admin/customers',               'admin/customers.php', 'index'],
        ['GET',  '/admin/coupons',                 'admin/coupons.php',   'index'],
        ['POST', '/admin/coupons/save',            'admin/coupons.php',   'save'],
        ['POST', '/admin/coupons/{id}/delete',     'admin/coupons.php',   'delete'],
        ['GET',  '/admin/messages',                'admin/messages.php',  'index'],
        ['POST', '/admin/messages/{id}/read',      'admin/messages.php',  'mark_read'],
        ['GET',  '/admin/settings',                'admin/settings.php',  'index'],
        ['POST', '/admin/settings',                'admin/settings.php',  'save'],
    ];
}

/** الگوی مسیر را به regex تبدیل می‌کند */
function route_to_regex(string $pattern): string
{
    $quoted = preg_quote($pattern, '#');
    $regex = preg_replace('#\\\\\{([a-z_]+)\\\\\}#i', '(?P<$1>[^/]+)', $quoted) ?? $quoted;
    return '#^' . $regex . '$#u';
}

/**
 * یافتن کنترلر مناسب برای مسیر
 * @return array{0:string,1:string,2:array}|null
 */
function route_resolve(string $path, string $method): ?array
{
    $path = '/' . trim($path, '/');
    if ($path === '//') {
        $path = '/';
    }

    $methodMatchedButPathNotFound = false;

    foreach (routes() as [$routeMethod, $pattern, $file, $handler]) {
        if (!preg_match(route_to_regex($pattern), $path, $matches)) {
            continue;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = urldecode((string) $value);
            }
        }

        if ($routeMethod !== $method) {
            $methodMatchedButPathNotFound = true;
            continue;
        }

        return [$file, $handler, $params];
    }

    if ($methodMatchedButPathNotFound) {
        http_response_code(405);
        header('Allow: GET, POST');
    }

    return null;
}
