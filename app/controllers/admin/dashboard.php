<?php
/** داشبورد مدیریت */

declare(strict_types=1);

function index(): void
{
    require_admin();

    $series = admin_sales_series(7);
    $maxAmount = max(array_map(fn ($row) => $row['amount'], $series) ?: [0]) ?: 1;

    render('admin/dashboard', [
        'stats'       => admin_stats(),
        'series'      => $series,
        'maxAmount'   => $maxAmount,
        'latestOrders' => db_all(
            'SELECT * FROM orders ORDER BY created_at DESC LIMIT 6'
        ),
        'lowStock'    => db_all('SELECT * FROM products WHERE stock <= 3 ORDER BY stock ASC LIMIT 5'),
        'topProducts' => products_best_sellers(5),
        'messages'    => db_all('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 4'),
    ], ['title' => 'داشبورد']);
}
