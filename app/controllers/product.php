<?php
/**
 * صفحه محصول + ثبت دیدگاه
 */

declare(strict_types=1);

function show(string $slug): void
{
    $product = find_product($slug);
    if ($product === null) {
        render_error(404);
        return;
    }

    $stats = product_review_stats((int) $product['id']);
    $category = $product['category'] ?? null;

    $crumbs = [['label' => 'محصولات', 'url' => '/discounts']];
    if ($category !== null) {
        $crumbs[] = ['label' => $category['name'], 'url' => url(category_url($category))];
    }
    $crumbs[] = ['label' => $product['name'], 'url' => null];

    render('product', [
        'product'     => $product,
        'gallery'     => product_gallery($product),
        'specs'       => product_specs($product),
        'reviews'     => product_reviews((int) $product['id']),
        'reviewStats' => $stats,
        'related'     => products_related($product, 4),
        'category'    => $category,
        'breadcrumbs' => $crumbs,
    ], [
        'title'       => $product['name'],
        'description' => mb_substr((string) ($product['short_desc'] ?: $product['description']), 0, 180),
        'og_image'    => product_image($product['image']),
        'body_class'  => 'page-product',
    ]);
}

/** ثبت دیدگاه کاربر برای محصول */
function review(string $slug): void
{
    $product = find_product($slug);
    if ($product === null) {
        render_error(404);
        return;
    }

    $user = current_user();
    $name = clean(input('name', $user['name'] ?? ''), 80);
    $comment = clean(input('comment', ''), 800);
    $rating = max(1, min(5, (int) input('rating', 5)));

    if (mb_strlen($name) < 3 || mb_strlen($comment) < 10) {
        if (is_ajax()) {
            json_response(['ok' => false, 'message' => 'نام و متن دیدگاه را کامل وارد کنید (حداقل ۱۰ کاراکتر).'], 422);
        }
        flash_error('نام و متن دیدگاه را کامل وارد کنید.');
        redirect(product_url($product));
    }

    db_insert('product_reviews', [
        'product_id'  => (int) $product['id'],
        'user_id'     => $user ? (int) $user['id'] : null,
        'name'        => $name,
        'rating'      => $rating,
        'comment'     => $comment,
        'is_approved' => 1,
        'created_at'  => date('Y-m-d H:i:s'),
    ]);

    // بروزرسانی میانگین امتیاز محصول
    $stats = product_review_stats((int) $product['id']);
    db_run(
        'UPDATE products SET rating = :rating, rating_count = :count WHERE id = :id',
        ['rating' => $stats['avg'], 'count' => $stats['total'], 'id' => (int) $product['id']]
    );

    if (is_ajax()) {
        json_response(['ok' => true, 'message' => 'دیدگاه شما ثبت شد. ممنون از همراهی‌تان!']);
    }

    flash_success('دیدگاه شما با موفقیت ثبت شد.');
    redirect(product_url($product));
}
