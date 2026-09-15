<?php
/**
 * فرآیند پرداخت، درگاه (شبیه‌سازی‌شده) و پیگیری سفارش
 */

declare(strict_types=1);

function index(): void
{
    $totals = cart_totals();
    if ($totals['items'] === []) {
        flash_error('سبد خرید شما خالی است.');
        redirect('/cart');
    }

    $user = current_user();
    $addresses = $user ? db_all('SELECT * FROM addresses WHERE user_id = :id ORDER BY is_default DESC, id DESC', ['id' => (int) $user['id']]) : [];

    render('checkout', [
        'totals'          => $totals,
        'user'            => $user,
        'addresses'       => $addresses,
        'shippingMethods' => shipping_methods(),
        'provinces'       => provinces(),
    ], [
        'title'      => 'تکمیل سفارش',
        'no_index'   => true,
        'body_class' => 'page-checkout',
    ]);
}

/** ثبت سفارش */
function store(): void
{
    $totals = cart_totals((string) input('shipping_method', 'post'));
    if ($totals['items'] === []) {
        flash_error('سبد خرید شما خالی است.');
        redirect('/cart');
    }

    $name = clean(input('customer_name', ''), 120);
    $phone = en_digits((string) input('customer_phone', ''));
    $email = clean(input('customer_email', ''), 120);
    $province = clean(input('province', ''), 60);
    $city = clean(input('city', ''), 60);
    $address = clean(input('address', ''), 400);
    $postalCode = clean(input('postal_code', ''), 20);
    $note = clean(input('note', ''), 500);
    $paymentMethod = input('payment_method', 'online') === 'cod' ? 'cod' : 'online';
    $shippingMethod = array_key_exists((string) input('shipping_method', 'post'), shipping_methods())
        ? (string) input('shipping_method', 'post')
        : 'post';

    $errors = [];
    if (mb_strlen($name) < 3) {
        $errors['customer_name'] = 'نام و نام خانوادگی را کامل وارد کنید.';
    }
    if (preg_match('/^09\d{9}$/', $phone) !== 1) {
        $errors['customer_phone'] = 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.';
    }
    if ($province === '' || $city === '' || mb_strlen($address) < 10) {
        $errors['address'] = 'استان، شهر و نشانی کامل را وارد کنید.';
    }
    if ($paymentMethod === 'online' && setting('online_enabled') !== '1') {
        $errors['payment_method'] = 'پرداخت آنلاین موقتاً غیرفعال است.';
    }

    if ($errors !== []) {
        old_keep($_POST);
        flash_error(implode(' ', $errors));
        redirect('/checkout');
    }
    old_clear();

    $userId = ensure_user_for_order($name, $phone, $email, $address);
    $code = generate_order_code();
    $items = $totals['items'];

    $orderId = db_insert('orders', [
        'code'            => $code,
        'user_id'         => $userId,
        'customer_name'   => $name,
        'customer_phone'  => $phone,
        'customer_email'  => $email ?: null,
        'province'        => $province,
        'city'            => $city,
        'address'         => $address,
        'postal_code'     => $postalCode ?: null,
        'note'            => $note ?: null,
        'items_count'     => $totals['count'],
        'subtotal'        => $totals['subtotal'],
        'discount'        => $totals['discount'],
        'coupon_code'     => $totals['coupon']['code'] ?? null,
        'shipping'        => $totals['shipping'],
        'shipping_method' => $shippingMethod,
        'total'           => $totals['total'],
        'payment_method'  => $paymentMethod,
        'payment_status'  => 'unpaid',
        'status'          => 'pending',
        'created_at'      => date('Y-m-d H:i:s'),
    ]);

    foreach ($items as $item) {
        db_insert('order_items', [
            'order_id'   => $orderId,
            'product_id' => (int) $item['product']['id'],
            'name'       => $item['product']['name'],
            'slug'       => $item['product']['slug'],
            'image'      => $item['product']['image'],
            'brand'      => $item['product']['brand'],
            'price'      => $item['unit_price'],
            'qty'        => $item['qty'],
        ]);

        // کاهش موجودی انبار
        db_run(
            'UPDATE products SET stock = MAX(0, stock - :qty) WHERE id = :id',
            ['qty' => $item['qty'], 'id' => (int) $item['product']['id']]
        );
    }

    coupon_consume($totals['coupon']['code'] ?? null);
    cart_clear();

    if ($paymentMethod === 'cod') {
        flash_success('سفارش شما ثبت شد. کارشناس فروش برای تأیید نهایی تماس می‌گیرد.');
        redirect('/order/' . $code);
    }

    redirect('/payment/' . $code);
}

/** صفحه درگاه پرداخت (در حالت واقعی به درگاه بانکی هدایت می‌شود) */
function payment(string $code): void
{
    $order = find_order($code);
    if ($order === null) {
        render_error(404);
        return;
    }
    if ($order['payment_status'] === 'paid') {
        redirect('/order/' . $code);
    }

    render('payment', [
        'order'  => $order,
        'gateway' => setting('gateway', 'mock'),
    ], [
        'title'      => 'پرداخت سفارش ' . $code,
        'no_index'   => true,
        'body_class' => 'page-payment',
    ]);
}

/** نتیجه پرداخت (بازگشت از درگاه) */
function payment_post(string $code): void
{
    $order = find_order($code);
    if ($order === null) {
        render_error(404);
        return;
    }

    $success = input('result', 'success') === 'success';

    if (!$success) {
        db_update('orders', [
            'payment_status' => 'failed',
            'updated_at'     => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => (int) $order['id']]);

        flash_error('پرداخت انجام نشد. می‌توانید دوباره تلاش کنید یا سفارش را با پرداخت در محل ثبت کنید.');
        redirect('/order/' . $code);
    }

    $reference = 'ZP' . random_int(100000000, 999999999);

    db_update('orders', [
        'payment_status' => 'paid',
        'payment_ref'    => $reference,
        'status'         => 'processing',
        'updated_at'     => date('Y-m-d H:i:s'),
    ], 'id = :id', ['id' => (int) $order['id']]);

    flash_success('پرداخت شما با موفقیت انجام شد. کد پیگیری: ' . fa_digits($reference));
    redirect('/order/' . $code);
}

/** بازگشت از درگاه (در حالت واقعی پارامترهای درگاه اینجا بررسی می‌شوند) */
function verify(): void
{
    $code = clean($_GET['code'] ?? '', 40);
    redirect($code !== '' ? '/order/' . $code : '/account/orders');
}

/** نمایش جزئیات سفارش (صفحه موفقیت سفارش) */
function show(string $code): void
{
    $order = find_order($code);
    if ($order === null) {
        render_error(404);
        return;
    }

    $user = current_user();
    $isOwner = $user !== null && (int) $order['user_id'] === (int) $user['id'];

    render('order', [
        'order'       => $order,
        'isOwner'     => $isOwner,
        'showDetails' => true,
        'fromPayment' => isset($_GET['payment']),
    ], [
        'title'      => 'سفارش ' . $order['code'],
        'no_index'   => true,
        'body_class' => 'page-order',
    ]);
}

/** فرم پیگیری سفارش */
function track(): void
{
    render('track', [
        'order' => null,
        'code'  => '',
    ], [
        'title'      => 'پیگیری سفارش',
        'body_class' => 'page-track',
    ]);
}

/** بررسی کد پیگیری */
function track_post(): void
{
    $code = clean(input('code', ''), 40);

    // پشتیبانی از ورود «شماره سفارش» یا «شماره موبایل»
    $order = find_order($code);
    if ($order === null && preg_match('/^09\d{9}$/', en_digits($code)) === 1) {
        $order = db_one(
            'SELECT * FROM orders WHERE customer_phone = :phone ORDER BY created_at DESC LIMIT 1',
            ['phone' => en_digits($code)]
        );
        if ($order !== null) {
            $order['items'] = db_all('SELECT * FROM order_items WHERE order_id = :id', ['id' => (int) $order['id']]);
        }
    }

    if ($order === null) {
        flash_error('سفارشی با این کد پیدا نشد. کد را از پیام تأیید سفارش بررسی کنید.');
        redirect('/track');
    }

    redirect('/order/' . $order['code']);
}
