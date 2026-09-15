<?php
/**
 * لایه دسترسی به داده‌های فروشگاه (کاتالوگ، سفارش‌ها، مشتری‌ها)
 * همه کوئری‌ها با Prepared Statement نوشته شده‌اند.
 */

declare(strict_types=1);

/* -------------------------------------------------------------------------
 * دسته‌بندی‌ها
 * ---------------------------------------------------------------------- */

function nav_categories(): array
{
    static $categories = null;
    if ($categories !== null) {
        return $categories;
    }

    $categories = db_all(
        'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
         FROM categories c
         WHERE c.is_active = 1
         ORDER BY c.position, c.id'
    );

    return $categories;
}

function category_url(array $category): string
{
    return '/category/' . $category['id'] . '-' . $category['slug'];
}

function category_children(int $parentId): array
{
    return array_values(array_filter(nav_categories(), fn ($c) => (int) $c['parent_id'] === $parentId));
}

/** یافتن دسته‌بندی با «۱۲-نام-لاتین» یا فقط «نام-لاتین» */
function find_category(string $slug): ?array
{
    if (preg_match('/^(\d+)/', $slug, $m) === 1) {
        $category = db_one('SELECT * FROM categories WHERE id = :id AND is_active = 1', ['id' => (int) $m[1]]);
        if ($category !== null) {
            return $category;
        }
    }

    return db_one('SELECT * FROM categories WHERE slug = :slug AND is_active = 1', ['slug' => $slug]);
}

/** شناسه دسته‌بندی به همراه زیرمجموعه‌ها (برای فیلتر محصولات) */
function category_with_children(int $categoryId): array
{
    $ids = [$categoryId];
    foreach (nav_categories() as $category) {
        if ((int) $category['parent_id'] === $categoryId) {
            $ids[] = (int) $category['id'];
        }
    }
    return $ids;
}

/* -------------------------------------------------------------------------
 * محصولات
 * ---------------------------------------------------------------------- */

function product_url(array $product): string
{
    return '/product/' . $product['id'] . '-' . $product['slug'];
}

/** یافتن محصول با «۱۲-نام-لاتین» یا slug خالی */
function find_product(string $slug): ?array
{
    $product = null;

    if (preg_match('/^(\d+)/', $slug, $m) === 1) {
        $product = db_one('SELECT * FROM products WHERE id = :id AND is_active = 1', ['id' => (int) $m[1]]);
    }
    if ($product === null) {
        $product = db_one('SELECT * FROM products WHERE slug = :slug AND is_active = 1', ['slug' => $slug]);
    }

    if ($product !== null) {
        $product['category'] = db_one('SELECT * FROM categories WHERE id = :id', ['id' => (int) $product['category_id']]);
    }

    return $product;
}

/** قیمت نهایی محصول (با احتساب تخفیف) */
function product_price(array $product): int
{
    $sale = (int) ($product['sale_price'] ?? 0);
    $price = (int) $product['price'];
    return ($sale > 0 && $sale < $price) ? $sale : $price;
}

function product_is_available(array $product): bool
{
    return (int) $product['stock'] > 0;
}

/** برچسب‌های نمایشی محصول */
function product_badges(array $product): array
{
    $badges = [];
    if (discount_percent((int) $product['price'], (int) $product['sale_price']) > 0) {
        $badges[] = ['label' => '٪' . fa_digits(discount_percent((int) $product['price'], (int) $product['sale_price'])) . ' تخفیف', 'class' => 'badge-sale'];
    }
    if ((int) $product['stock'] <= 0) {
        $badges[] = ['label' => 'ناموجود', 'class' => 'badge-muted'];
    } elseif ((int) $product['stock'] <= 3) {
        $badges[] = ['label' => 'تنها ' . fa_digits((int) $product['stock']) . ' عدد', 'class' => 'badge-warn'];
    }
    if (strtotime((string) $product['created_at']) > strtotime('-14 days')) {
        $badges[] = ['label' => 'جدید', 'class' => 'badge-new'];
    }
    return $badges;
}

/**
 * فهرست محصولات با فیلتر و صفحه‌بندی
 * @param array{category?:int,q?:string,min_price?:int,max_price?:int,brand?:string[],in_stock?:bool,sort?:string,page?:int,per_page?:int,only_special?:bool} $filters
 */
function products_query(array $filters = []): array
{
    $where = ['p.is_active = 1'];
    $params = [];

    if (!empty($filters['category'])) {
        $ids = category_with_children((int) $filters['category']);
        $placeholders = [];
        foreach ($ids as $index => $id) {
            $key = 'cat' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }
        $where[] = 'p.category_id IN (' . implode(', ', $placeholders) . ')';
    }

    if (!empty($filters['q'])) {
        $where[] = '(p.name LIKE :q OR p.brand LIKE :q OR p.short_desc LIKE :q OR p.description LIKE :q OR p.sku LIKE :q)';
        $params['q'] = '%' . trim((string) $filters['q']) . '%';
    }

    if (!empty($filters['min_price'])) {
        $where[] = 'COALESCE(NULLIF(p.sale_price, 0), p.price) >= :min_price';
        $params['min_price'] = (int) $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[] = 'COALESCE(NULLIF(p.sale_price, 0), p.price) <= :max_price';
        $params['max_price'] = (int) $filters['max_price'];
    }

    if (!empty($filters['brand']) && is_array($filters['brand'])) {
        $brandPlaceholders = [];
        foreach (array_values($filters['brand']) as $index => $brand) {
            $key = 'brand' . $index;
            $brandPlaceholders[] = ':' . $key;
            $params[$key] = $brand;
        }
        $where[] = 'p.brand IN (' . implode(', ', $brandPlaceholders) . ')';
    }

    if (!empty($filters['in_stock'])) {
        $where[] = 'p.stock > 0';
    }

    if (!empty($filters['only_special'])) {
        $where[] = 'p.is_special = 1 AND p.sale_price > 0';
    }

    $orderBy = match ($filters['sort'] ?? 'newest') {
        'cheap'      => 'COALESCE(NULLIF(p.sale_price, 0), p.price) ASC',
        'expensive'  => 'COALESCE(NULLIF(p.sale_price, 0), p.price) DESC',
        'popular'    => 'p.rating DESC, p.rating_count DESC',
        'discount'   => '((p.price - p.sale_price) * 100.0 / p.price) DESC',
        'name'       => 'p.name COLLATE NOCASE ASC',
        default      => 'p.created_at DESC, p.id DESC',
    };

    $perPage = max(1, (int) ($filters['per_page'] ?? config('per_page', 12)));
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $whereSql = implode(' AND ', $where);

    $total = (int) db_value("SELECT COUNT(*) FROM products p WHERE {$whereSql}", $params, 0);

    $items = db_all(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE {$whereSql}
         ORDER BY {$orderBy}
         LIMIT {$perPage} OFFSET {$offset}",
        $params
    );

    return ['items' => $items, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
}

function products_featured(int $limit = 8): array
{
    return db_all(
        'SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1 AND p.is_featured = 1
         ORDER BY p.created_at DESC LIMIT ' . max(1, $limit)
    );
}

function products_special(int $limit = 8): array
{
    return db_all(
        'SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1 AND p.is_special = 1 AND p.sale_price > 0
         ORDER BY p.special_ends_at ASC LIMIT ' . max(1, $limit)
    );
}

function products_latest(int $limit = 8): array
{
    return db_all(
        'SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1
         ORDER BY p.created_at DESC LIMIT ' . max(1, $limit)
    );
}

function products_best_sellers(int $limit = 8): array
{
    return db_all(
        'SELECT p.*, c.name AS category_name,
                (SELECT COALESCE(SUM(oi.qty), 0) FROM order_items oi WHERE oi.product_id = p.id) AS sold
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1
         ORDER BY sold DESC, p.rating DESC LIMIT ' . max(1, $limit)
    );
}

function products_related(array $product, int $limit = 4): array
{
    return db_all(
        'SELECT p.* FROM products p
         WHERE p.is_active = 1 AND p.category_id = :category AND p.id <> :id
         ORDER BY p.rating DESC LIMIT ' . max(1, $limit),
        ['category' => (int) $product['category_id'], 'id' => (int) $product['id']]
    );
}

function products_search_suggestions(string $term, int $limit = 6): array
{
    if (mb_strlen(trim($term)) < 2) {
        return [];
    }
    return db_all(
        'SELECT id, name, slug, price, sale_price, image FROM products
         WHERE is_active = 1 AND (name LIKE :q OR brand LIKE :q)
         ORDER BY rating DESC LIMIT ' . max(1, $limit),
        ['q' => '%' . trim($term) . '%']
    );
}

function catalog_brands(?int $categoryId = null): array
{
    $sql = 'SELECT brand, COUNT(*) AS total FROM products WHERE is_active = 1 AND brand IS NOT NULL AND brand <> ""';
    $params = [];
    if ($categoryId) {
        $ids = category_with_children($categoryId);
        $sql .= ' AND category_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')';
        $params = $ids;
    }
    $sql .= ' GROUP BY brand ORDER BY total DESC, brand ASC';
    return db_all($sql, $params);
}

function catalog_price_bounds(?int $categoryId = null): array
{
    $sql = 'SELECT MIN(COALESCE(NULLIF(sale_price, 0), price)) AS min_price, MAX(COALESCE(NULLIF(sale_price, 0), price)) AS max_price
            FROM products WHERE is_active = 1';
    $params = [];
    if ($categoryId) {
        $ids = category_with_children($categoryId);
        $sql .= ' AND category_id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')';
        $params = $ids;
    }
    $bounds = db_one($sql, $params) ?: ['min_price' => 0, 'max_price' => 0];

    return [
        'min' => (int) ($bounds['min_price'] ?? 0),
        'max' => (int) ($bounds['max_price'] ?? 0),
    ];
}

function product_gallery(array $product): array
{
    $images = [];
    if (!empty($product['image'])) {
        $images[] = $product['image'];
    }
    $gallery = json_decode((string) ($product['gallery'] ?? '[]'), true);
    if (is_array($gallery)) {
        foreach ($gallery as $image) {
            if (is_string($image) && $image !== '' && !in_array($image, $images, true)) {
                $images[] = $image;
            }
        }
    }
    return $images;
}

function product_specs(array $product): array
{
    $specs = json_decode((string) ($product['specs'] ?? '[]'), true);
    return is_array($specs) ? $specs : [];
}

/* -------------------------------------------------------------------------
 * نظرات محصولات
 * ---------------------------------------------------------------------- */

function product_reviews(int $productId, int $limit = 20): array
{
    return db_all(
        'SELECT * FROM product_reviews WHERE product_id = :id AND is_approved = 1
         ORDER BY created_at DESC LIMIT ' . max(1, $limit),
        ['id' => $productId]
    );
}

function product_review_stats(int $productId): array
{
    $row = db_one(
        'SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS avg_rating
         FROM product_reviews WHERE product_id = :id AND is_approved = 1',
        ['id' => $productId]
    );
    return [
        'total' => (int) ($row['total'] ?? 0),
        'avg'   => round((float) ($row['avg_rating'] ?? 0), 1),
    ];
}

/* -------------------------------------------------------------------------
 * کدهای تخفیف
 * ---------------------------------------------------------------------- */

function find_coupon(string $code): ?array
{
    $code = mb_strtoupper(trim($code));
    $coupon = db_one('SELECT * FROM coupons WHERE UPPER(code) = :code AND is_active = 1', ['code' => $code]);
    if ($coupon === null) {
        return null;
    }
    if (!empty($coupon['expires_at']) && strtotime((string) $coupon['expires_at']) < time()) {
        return null;
    }
    if ((int) $coupon['max_uses'] > 0 && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
        return null;
    }
    return $coupon;
}

function coupon_amount(array $coupon, int $subtotal): int
{
    if ((int) $coupon['min_order'] > $subtotal) {
        return 0;
    }
    return $coupon['type'] === 'percent'
        ? (int) round($subtotal * ((int) $coupon['amount'] / 100))
        : (int) $coupon['amount'];
}

/* -------------------------------------------------------------------------
 * سفارش‌ها
 * ---------------------------------------------------------------------- */

const ORDER_STATUSES = [
    'pending'    => 'در انتظار پرداخت',
    'paid'       => 'پرداخت شده',
    'processing' => 'در حال آماده‌سازی',
    'shipped'    => 'ارسال شده',
    'delivered'  => 'تحویل داده شده',
    'canceled'   => 'لغو شده',
];

function order_status_label(string $status): string
{
    return ORDER_STATUSES[$status] ?? $status;
}

function order_status_class(string $status): string
{
    return match ($status) {
        'paid', 'delivered' => 'status-ok',
        'processing', 'shipped' => 'status-info',
        'canceled' => 'status-danger',
        default => 'status-warn',
    };
}

function find_order(string $code): ?array
{
    $order = db_one('SELECT * FROM orders WHERE code = :code', ['code' => trim($code)]);
    if ($order === null) {
        return null;
    }
    $order['items'] = db_all('SELECT * FROM order_items WHERE order_id = :id', ['id' => (int) $order['id']]);
    return $order;
}

function user_orders(int $userId, int $limit = 30): array
{
    return db_all(
        'SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
         FROM orders o WHERE o.user_id = :id ORDER BY o.created_at DESC LIMIT ' . max(1, $limit),
        ['id' => $userId]
    );
}

function generate_order_code(): string
{
    return 'SZ-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/* -------------------------------------------------------------------------
 * آمار پنل مدیریت
 * ---------------------------------------------------------------------- */

function admin_stats(): array
{
    return [
        'orders_today'   => (int) db_value("SELECT COUNT(*) FROM orders WHERE date(created_at) = date('now','localtime')", [], 0),
        'orders_total'   => (int) db_value('SELECT COUNT(*) FROM orders', [], 0),
        'revenue_month'  => (int) db_value(
            "SELECT COALESCE(SUM(total), 0) FROM orders
             WHERE status IN ('paid','processing','shipped','delivered')
               AND created_at >= date('now','localtime','start of month')",
            [],
            0
        ),
        'revenue_total'  => (int) db_value(
            "SELECT COALESCE(SUM(total), 0) FROM orders WHERE status IN ('paid','processing','shipped','delivered')",
            [],
            0
        ),
        'customers'      => (int) db_value("SELECT COUNT(*) FROM users WHERE role = 'customer'", [], 0),
        'products'       => (int) db_value('SELECT COUNT(*) FROM products', [], 0),
        'low_stock'      => (int) db_value('SELECT COUNT(*) FROM products WHERE stock <= 3', [], 0),
        'pending_orders' => (int) db_value("SELECT COUNT(*) FROM orders WHERE status IN ('pending','paid')", [], 0),
        'messages_new'   => (int) db_value('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0', [], 0),
    ];
}

/** فروش ۷ روز اخیر برای نمودار کوچک داشبورد */
function admin_sales_series(int $days = 7): array
{
    $series = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} day"));
        $series[] = [
            'date'   => $date,
            'label'  => jdate($date, 'day_month'),
            'amount' => (int) db_value(
                "SELECT COALESCE(SUM(total), 0) FROM orders
                 WHERE date(created_at) = :date AND status IN ('paid','processing','shipped','delivered')",
                ['date' => $date],
                0
            ),
        ];
    }
    return $series;
}
