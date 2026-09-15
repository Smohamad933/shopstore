<?php
/** فهرست مشتریان */

declare(strict_types=1);

function index(): void
{
    require_admin();

    $term = clean($_GET['q'] ?? '', 80);
    $where = ["role = 'customer'"];
    $params = [];

    if ($term !== '') {
        $where[] = '(name LIKE :q OR email LIKE :q OR phone LIKE :q)';
        $params['q'] = '%' . $term . '%';
    }

    $whereSql = implode(' AND ', $where);

    $customers = db_all(
        "SELECT u.*,
                (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders_count,
                (SELECT COALESCE(SUM(o.total), 0) FROM orders o WHERE o.user_id = u.id AND o.status IN ('paid','processing','shipped','delivered')) AS total_spent
         FROM users u WHERE {$whereSql} ORDER BY u.created_at DESC LIMIT 100",
        $params
    );

    render_admin('admin/customers', [
        'customers' => $customers,
        'term'      => $term,
    ], ['title' => 'مشتریان']);
}
