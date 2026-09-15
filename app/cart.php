<?php
/**
 * سبد خرید (بر پایه نشست) + محاسبه هزینه ارسال و کد تخفیف
 */

declare(strict_types=1);

/** ساختار سبد در نشست: [product_id => qty] */
function cart_raw(): array
{
    $cart = $_SESSION['cart'] ?? [];
    return is_array($cart) ? $cart : [];
}

function cart_save(array $cart): void
{
    $_SESSION['cart'] = array_filter($cart, fn ($qty) => (int) $qty > 0);
}

function cart_add(int $productId, int $qty = 1): array
{
    $product = db_one('SELECT * FROM products WHERE id = :id AND is_active = 1', ['id' => $productId]);
    if ($product === null) {
        return ['ok' => false, 'message' => 'محصول یافت نشد.'];
    }
    if ((int) $product['stock'] <= 0) {
        return ['ok' => false, 'message' => 'این محصول فعلاً موجود نیست.'];
    }

    $cart = cart_raw();
    $current = (int) ($cart[$productId] ?? 0);
    $newQty = min($current + max(1, $qty), (int) $product['stock']);
    $cart[$productId] = $newQty;
    cart_save($cart);

    return ['ok' => true, 'qty' => $newQty, 'message' => '«' . $product['name'] . '» به سبد خرید اضافه شد.'];
}

function cart_set(int $productId, int $qty): array
{
    $product = db_one('SELECT id, name, stock FROM products WHERE id = :id AND is_active = 1', ['id' => $productId]);
    if ($product === null) {
        return ['ok' => false, 'message' => 'محصول یافت نشد.'];
    }

    $cart = cart_raw();
    if ($qty <= 0) {
        unset($cart[$productId]);
        cart_save($cart);
        return ['ok' => true, 'qty' => 0, 'message' => 'محصول از سبد حذف شد.'];
    }

    $qty = min($qty, max(1, (int) $product['stock']));
    $cart[$productId] = $qty;
    cart_save($cart);

    return [
        'ok'      => true,
        'qty'     => $qty,
        'message' => (int) $product['stock'] < $qty ? 'بیشتر از موجودی انبار امکان‌پذیر نیست.' : 'سبد خرید بروزرسانی شد.',
    ];
}

function cart_remove(int $productId): void
{
    $cart = cart_raw();
    unset($cart[$productId]);
    cart_save($cart);
}

function cart_clear(): void
{
    unset($_SESSION['cart'], $_SESSION['coupon']);
}

/** آیتم‌های سبد به همراه اطلاعات کامل محصول */
function cart_items(): array
{
    $cart = cart_raw();
    if ($cart === []) {
        return [];
    }

    $items = [];
    foreach ($cart as $productId => $qty) {
        $product = db_one('SELECT * FROM products WHERE id = :id AND is_active = 1', ['id' => (int) $productId]);
        if ($product === null) {
            unset($cart[$productId]);
            continue;
        }
        $unitPrice = product_price($product);
        $items[] = [
            'product'    => $product,
            'qty'        => (int) $qty,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * (int) $qty,
            'in_stock'   => (int) $product['stock'] >= (int) $qty,
        ];
    }
    cart_save($cart);

    return $items;
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart_raw()));
}

function cart_subtotal(array $items = null): int
{
    $items = $items ?? cart_items();
    return array_sum(array_map(fn ($item) => $item['line_total'], $items));
}

/** اعمال کد تخفیف */
function coupon_apply(string $code): array
{
    $code = trim($code);
    if ($code === '') {
        unset($_SESSION['coupon']);
        return ['ok' => true, 'message' => 'کد تخفیف حذف شد.'];
    }

    $coupon = find_coupon($code);
    if ($coupon === null) {
        return ['ok' => false, 'message' => 'این کد تخفیف معتبر نیست یا منقضی شده است.'];
    }

    $subtotal = cart_subtotal();
    if ((int) $coupon['min_order'] > $subtotal) {
        return [
            'ok' => false,
            'message' => 'این کد برای سفارش‌های بالای ' . toman((int) $coupon['min_order']) . ' فعال است.',
        ];
    }

    $_SESSION['coupon'] = $coupon['code'];

    return [
        'ok'      => true,
        'message' => 'کد تخفیف «' . $coupon['code'] . '» اعمال شد.',
        'amount'  => coupon_amount($coupon, $subtotal),
    ];
}

function cart_coupon(): ?array
{
    $code = $_SESSION['coupon'] ?? null;
    return $code ? find_coupon((string) $code) : null;
}

/** محاسبه هزینه ارسال بر اساس مبلغ سبد */
function shipping_cost(int $subtotal, string $method = 'post'): int
{
    if ($subtotal <= 0) {
        return 0;
    }

    if ($method === 'pickup') {
        return 0; // تحویل حضوری از انبار
    }

    $freeFrom = (int) setting('free_shipping_from', '0');
    if ($freeFrom > 0 && $subtotal >= $freeFrom) {
        return 0;
    }

    $flat = (int) setting('shipping_flat', '0');
    return $method === 'express' ? (int) round($flat * 1.8) : $flat;
}

function shipping_methods(): array
{
    return [
        'post'    => ['label' => 'ارسال با باربری / پست پیشتاز', 'note' => '۲ تا ۴ روز کاری'],
        'express' => ['label' => 'ارسال فوری با پیک', 'note' => 'همان روز در تهران'],
        'pickup'  => ['label' => 'تحویل حضوری از انبار', 'note' => 'تهران، جاده مخصوص'],
    ];
}

/** جمع‌بندی مالی سبد */
function cart_totals(string $shippingMethod = 'post'): array
{
    $items = cart_items();
    $subtotal = cart_subtotal($items);
    $coupon = cart_coupon();
    $discount = $coupon ? coupon_amount($coupon, $subtotal) : 0;
    $payable = max(0, $subtotal - $discount);
    $shipping = shipping_cost($payable, $shippingMethod);

    return [
        'items'      => $items,
        'count'      => cart_count(),
        'subtotal'   => $subtotal,
        'discount'   => $discount,
        'coupon'     => $coupon,
        'shipping'   => $shipping,
        'payable'    => $payable,
        'total'      => $payable + $shipping,
        'free_shipping_remaining' => max(0, (int) setting('free_shipping_from', '0') - $payable),
    ];
}

/** خروجی JSON برای درخواست‌های AJAX سبد خرید */
function cart_json(string $shippingMethod = 'post'): array
{
    $totals = cart_totals($shippingMethod);

    return [
        'ok'    => true,
        'count' => $totals['count'],
        'items' => array_map(fn ($item) => [
            'id'         => (int) $item['product']['id'],
            'name'       => $item['product']['name'],
            'url'        => product_url($item['product']),
            'image'      => product_image($item['product']['image']),
            'qty'        => $item['qty'],
            'unit_price' => money($item['unit_price']),
            'line_total' => money($item['line_total']),
            'stock'      => (int) $item['product']['stock'],
        ], $totals['items']),
        'totals' => [
            'subtotal'   => money($totals['subtotal']),
            'discount'   => money($totals['discount']),
            'shipping'   => money($totals['shipping'], true),
            'total'      => money($totals['total']),
            'total_raw'  => $totals['total'],
            'coupon'     => $totals['coupon']['code'] ?? null,
            'free_shipping_remaining' => money($totals['free_shipping_remaining']),
        ],
    ];
}

/** ثبت استفاده از کد تخفیف بعد از سفارش موفق */
function coupon_consume(?string $code): void
{
    if (!$code) {
        return;
    }
    db_run('UPDATE coupons SET used_count = used_count + 1 WHERE UPPER(code) = :code', ['code' => mb_strtoupper($code)]);
}
