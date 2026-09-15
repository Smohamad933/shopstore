<?php
/**
 * صفحه دسته‌بندی با فیلترها (برند، بازه قیمت، موجودی، مرتب‌سازی، صفحه‌بندی)
 */

declare(strict_types=1);

function show(string $slug): void
{
    $category = find_category($slug);
    if ($category === null) {
        render_error(404);
        return;
    }

    $categoryId = (int) $category['id'];
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $sort = (string) ($_GET['sort'] ?? 'newest');
    $brands = isset($_GET['brand']) ? array_values(array_filter((array) $_GET['brand'], 'is_string')) : [];
    $minPrice = max(0, (int) en_digits((string) ($_GET['min_price'] ?? '0')));
    $maxPrice = max(0, (int) en_digits((string) ($_GET['max_price'] ?? '0')));
    $inStock = !empty($_GET['in_stock']);

    $result = products_query([
        'category'  => $categoryId,
        'brand'     => $brands,
        'min_price' => $minPrice,
        'max_price' => $maxPrice,
        'in_stock'  => $inStock,
        'sort'      => $sort,
        'page'      => $page,
        'per_page'  => (int) config('per_page', 12),
    ]);

    $query = array_filter([
        'sort'      => $sort,
        'brand'     => $brands,
        'min_price' => $minPrice ?: null,
        'max_price' => $maxPrice ?: null,
        'in_stock'  => $inStock ? 1 : null,
    ], fn ($value) => $value !== null && $value !== [] && $value !== '');

    $pagination = paginate($result['total'], $result['per_page'], $page, category_url($category), $query);
    $pagination['base'] = category_url($category);

    $children = category_children($categoryId);

    $crumbs = [['label' => 'دسته‌بندی‌ها', 'url' => '/']];
    if ((int) $category['parent_id'] > 0) {
        $parent = db_one('SELECT * FROM categories WHERE id = :id', ['id' => (int) $category['parent_id']]);
        if ($parent !== null) {
            $crumbs[] = ['label' => $parent['name'], 'url' => url(category_url($parent))];
        }
    }
    $crumbs[] = ['label' => $category['name'], 'url' => null];

    render('listing', [
        'heading'     => $category['name'],
        'description' => $category['description'],
        'category'    => $category,
        'children'    => $children,
        'products'    => $result['items'],
        'pagination'  => $pagination,
        'filters'     => [
            'sort'      => $sort,
            'brand'     => $brands,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'in_stock'  => $inStock,
        ],
        'brands'      => catalog_brands($categoryId),
        'priceBounds' => catalog_price_bounds($categoryId),
        'basePath'    => category_url($category),
        'query'       => $query,
        'breadcrumbs' => $crumbs,
        'showFilters' => true,
    ], [
        'title'       => $category['name'],
        'description' => $category['description'] ?: setting('site_description'),
        'body_class'  => 'page-category',
    ]);
}
