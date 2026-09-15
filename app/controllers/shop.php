<?php
/**
 * صفحات اصلی فروشگاه: خانه، جست‌وجو، تخفیف‌ها، نقشه سایت و API پیشنهاد جست‌وجو
 */

declare(strict_types=1);

function home(): void
{
    $specials = products_special(4);
    $featured = products_featured(8);
    $latest = products_latest(8);
    $bestSellers = products_best_sellers(4);
    $brands = array_slice(catalog_brands(), 0, 10);

    render('home', [
        'specials'    => $specials,
        'featured'    => $featured,
        'latest'      => $latest,
        'bestSellers' => $bestSellers,
        'brands'      => $brands,
        'categories'  => nav_categories(),
    ], [
        'title'       => null,
        'description' => setting('site_description'),
        'canonical'   => absolute_url('/'),
        'body_class'  => 'page-home',
    ]);
}

function search(): void
{
    $term = clean($_GET['q'] ?? '', 100);
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $sort = (string) ($_GET['sort'] ?? 'newest');

    $result = products_query([
        'q'        => $term,
        'sort'     => $sort,
        'page'     => $page,
        'per_page' => (int) config('per_page', 12),
    ]);

    $pagination = paginate($result['total'], $result['per_page'], $page, '/search', ['q' => $term, 'sort' => $sort]);
    $pagination['base'] = '/search';

    render('listing', [
        'heading'     => $term === '' ? 'جست‌وجو در محصولات' : 'نتایج جست‌وجو برای «' . $term . '»',
        'description' => $term === '' ? 'نام محصول، برند یا کد کالا را وارد کنید.' : null,
        'products'    => $result['items'],
        'pagination'  => $pagination,
        'filters'     => ['q' => $term, 'sort' => $sort],
        'basePath'    => '/search',
        'query'       => ['q' => $term, 'sort' => $sort],
        'breadcrumbs' => [['label' => 'جست‌وجو', 'url' => null]],
        'showFilters' => false,
        'categories'  => nav_categories(),
    ], [
        'title'      => $term === '' ? 'جست‌وجو' : 'جست‌وجو: ' . $term,
        'no_index'   => true,
        'body_class' => 'page-search',
    ]);
}

function discounts(): void
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $sort = (string) ($_GET['sort'] ?? 'discount');

    $result = products_query([
        'only_special' => true,
        'sort'         => $sort,
        'page'         => $page,
        'per_page'     => (int) config('per_page', 12),
    ]);

    $pagination = paginate($result['total'], $result['per_page'], $page, '/discounts', ['sort' => $sort]);
    $pagination['base'] = '/discounts';

    render('listing', [
        'heading'     => 'پیشنهادهای ویژه و تخفیف‌دار',
        'description' => 'تخفیف‌های فعال روی تجهیزات ساختمانی — تا پایان موجودی و زمان تخفیف.',
        'products'    => $result['items'],
        'pagination'  => $pagination,
        'filters'     => ['sort' => $sort],
        'basePath'    => '/discounts',
        'query'       => ['sort' => $sort],
        'breadcrumbs' => [['label' => 'تخفیف‌ها', 'url' => null]],
        'showFilters' => false,
    ], [
        'title'      => 'پیشنهادهای ویژه',
        'body_class' => 'page-discounts',
    ]);
}

/** پیشنهاد سریع جست‌وجو (AJAX) */
function api_search(): void
{
    $term = clean($_GET['q'] ?? '', 60);
    $items = array_map(fn ($product) => [
        'url'   => url(product_url($product)),
        'name'  => $product['name'],
        'image' => product_image($product['image']),
        'price' => money(product_price($product)),
    ], products_search_suggestions($term));

    json_response(['ok' => true, 'items' => $items]);
}

/** نقشه سایت XML */
function sitemap(): void
{
    header('Content-Type: application/xml; charset=utf-8');

    $urls = [
        ['loc' => absolute_url('/'), 'priority' => '1.0'],
        ['loc' => absolute_url('/discounts'), 'priority' => '0.8'],
        ['loc' => absolute_url('/about'), 'priority' => '0.5'],
        ['loc' => absolute_url('/contact'), 'priority' => '0.5'],
        ['loc' => absolute_url('/faq'), 'priority' => '0.4'],
        ['loc' => absolute_url('/terms'), 'priority' => '0.4'],
    ];

    foreach (nav_categories() as $category) {
        $urls[] = ['loc' => absolute_url(category_url($category)), 'priority' => '0.8'];
    }

    foreach (db_all('SELECT id, slug, updated_at, created_at FROM products WHERE is_active = 1 ORDER BY id') as $product) {
        $urls[] = [
            'loc'      => absolute_url(product_url($product)),
            'priority' => '0.7',
            'lastmod'  => date('Y-m-d', strtotime((string) ($product['updated_at'] ?: $product['created_at']))),
        ];
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $entry) {
        echo '  <url><loc>' . e($entry['loc']) . '</loc>';
        if (!empty($entry['lastmod'])) {
            echo '<lastmod>' . $entry['lastmod'] . '</lastmod>';
        }
        echo '<priority>' . ($entry['priority'] ?? '0.5') . '</priority></url>' . "\n";
    }
    echo '</urlset>';
}
