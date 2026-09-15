<?php
/**
 * سبد خرید: نمایش، افزودن، ویرایش تعداد، حذف و کد تخفیف
 * عملیات‌ها هم با فرم معمولی و هم با AJAX کار می‌کنند.
 */

declare(strict_types=1);

function index(): void
{
    $totals = cart_totals();
    $hasItems = $totals['items'] !== [];

    render('cart', [
        'totals'      => $totals,
        'suggestions' => $hasItems ? products_best_sellers(4) : products_featured(4),
    ], [
        'title'      => 'سبد خرید',
        'no_index'   => true,
        'body_class' => 'page-cart',
    ]);
}

function add(): void
{
    $productId = int_input('id');
    $qty = max(1, int_input('qty', 1));
    $result = cart_add($productId, $qty);

    if (is_ajax()) {
        json_response(array_merge($result, cart_json()));
    }

    if ($result['ok']) {
        flash_success($result['message']);
    } else {
        flash_error($result['message']);
    }
    redirect('/cart');
}

function update(): void
{
    $productId = int_input('id');
    $qty = int_input('qty', 1);
    $result = cart_set($productId, $qty);

    if (is_ajax()) {
        json_response(array_merge($result, cart_json()));
    }

    if (!$result['ok']) {
        flash_error($result['message']);
    }
    redirect('/cart');
}

function remove(): void
{
    $productId = int_input('id');
    cart_remove($productId);

    if (is_ajax()) {
        json_response(array_merge(['ok' => true, 'message' => 'محصول از سبد خرید حذف شد.'], cart_json()));
    }

    flash_success('محصول از سبد خرید حذف شد.');
    redirect('/cart');
}

/** اعمال یا حذف کد تخفیف */
function coupon(): void
{
    $code = clean(input('code', ''), 40);
    $result = coupon_apply($code);

    if (is_ajax()) {
        json_response(array_merge($result, cart_json()));
    }

    $result['ok'] ? flash_success($result['message']) : flash_error($result['message']);
    redirect('/cart');
}

/** خلاصه سبد خرید برای بروزرسانی نشانگر تعداد (AJAX) */
function api_summary(): void
{
    json_response(cart_json());
}
