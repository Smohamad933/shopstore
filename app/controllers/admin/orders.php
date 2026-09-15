<?php
/** مدیریت سفارش‌ها */

declare(strict_types=1);

function index(): void
{
    require_admin();

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 20;
    $status = (string) ($_GET['status'] ?? '');
    $term = clean($_GET['q'] ?? '', 80);

    $where = ['1 = 1'];
    $params = [];

    if ($status !== '' && array_key_exists($status, ORDER_STATUSES)) {
        $where[] = 'status = :status';
        $params['status'] = $status;
    }
    if ($term !== '') {
        $where[] = '(code LIKE :q OR customer_name LIKE :q OR customer_phone LIKE :q)';
        $params['q'] = '%' . $term . '%';
    }

    $whereSql = implode(' AND ', $where);
    $total = (int) db_value("SELECT COUNT(*) FROM orders WHERE {$whereSql}", $params, 0);
    $offset = ($page - 1) * $perPage;

    $orders = db_all(
        "SELECT * FROM orders WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
        $params
    );

    $pagination = paginate($total, $perPage, $page, '/admin/orders', array_filter([
        'status' => $status ?: null, 'q' => $term,
    ]));
    $pagination['base'] = '/admin/orders';

    render_admin('admin/orders', [
        'orders'      => $orders,
        'pagination'  => $pagination,
        'filters'     => ['status' => $status, 'q' => $term],
        'query'       => array_filter(['status' => $status ?: null, 'q' => $term]),
        'statusCounts' => db_all('SELECT status, COUNT(*) AS total FROM orders GROUP BY status'),
    ], ['title' => 'سفارش‌ها']);
}

function show(string $id): void
{
    require_admin();

    $order = db_one('SELECT * FROM orders WHERE id = :id', ['id' => (int) $id]);
    if ($order === null) {
        render_error(404);
        return;
    }
    $order['items'] = db_all('SELECT * FROM order_items WHERE order_id = :id', ['id' => (int) $order['id']]);

    render_admin('admin/order', [
        'order'  => $order,
        'customerOrders' => $order['user_id']
            ? db_all('SELECT code, total, status, created_at FROM orders WHERE user_id = :uid AND id <> :id ORDER BY created_at DESC LIMIT 5', [
                'uid' => (int) $order['user_id'], 'id' => (int) $order['id'],
            ])
            : [],
    ], ['title' => 'سفارش ' . $order['code']]);
}

/** تغییر وضعیت سفارش و ثبت کد رهگیری */
function status(string $id): void
{
    require_admin();

    $order = db_one('SELECT * FROM orders WHERE id = :id', ['id' => (int) $id]);
    if ($order === null) {
        flash_error('سفارش یافت نشد.');
        redirect('/admin/orders');
    }

    $newStatus = (string) input('status', $order['status']);
    if (!array_key_exists($newStatus, ORDER_STATUSES)) {
        flash_error('وضعیت انتخابی معتبر نیست.');
        redirect('/admin/orders/' . (int) $id);
    }

    $data = [
        'status'        => $newStatus,
        'tracking_code' => clean(input('tracking_code', ''), 60) ?: $order['tracking_code'],
        'admin_note'    => clean(input('admin_note', ''), 500) ?: $order['admin_note'],
        'updated_at'    => date('Y-m-d H:i:s'),
    ];

    // اگر سفارش به «پرداخت شده» تغییر کند و پرداخت ثبت نشده بود
    if ($newStatus === 'paid' && $order['payment_status'] !== 'paid') {
        $data['payment_status'] = 'paid';
        $data['payment_ref'] = $order['payment_ref'] ?: 'MANUAL' . random_int(10000, 99999);
    }

    db_update('orders', $data, 'id = :id', ['id' => (int) $id]);

    flash_success('وضعیت سفارش به «' . order_status_label($newStatus) . '» تغییر کرد.');
    redirect('/admin/orders/' . (int) $id);
}
