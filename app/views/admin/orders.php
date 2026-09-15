<?php
/**
 * فهرست سفارش‌ها
 * @var array $orders
 * @var array $pagination
 * @var array $filters
 * @var array $query
 * @var array $statusCounts
 */
$counts = [];
foreach ($statusCounts as $row) {
    $counts[$row['status']] = (int) $row['total'];
}
?>
<form class="toolbar-admin" method="get" action="<?= url('/admin/orders') ?>">
    <input class="input" type="search" name="q" placeholder="کد سفارش، نام یا موبایل مشتری" value="<?= e($filters['q']) ?>">
    <select class="select" name="status">
        <option value="">همه وضعیت‌ها</option>
        <?php foreach (ORDER_STATUSES as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                <?= e($label) ?> (<?= fa_digits($counts[$key] ?? 0) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-sm" type="submit">فیلتر</button>
    <a class="btn btn-sm btn-ghost" href="<?= url('/admin/orders') ?>">حذف فیلتر</a>
    <span class="muted small" style="margin-inline-start:auto;"><?= fa_digits($pagination['total']) ?> سفارش</span>
</form>

<div class="card">
    <?php if ($orders === []): ?>
        <div class="empty-admin">سفارشی با این مشخصات پیدا نشد.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>کد سفارش</th>
                    <th>مشتری</th>
                    <th>مبلغ</th>
                    <th>پرداخت</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="nowrap strong"><?= e($order['code']) ?></td>
                        <td>
                            <?= e($order['customer_name']) ?>
                            <div class="tiny muted"><?= fa_digits((string) $order['customer_phone']) ?> • <?= fa_digits((int) $order['items_count']) ?> قلم</div>
                        </td>
                        <td class="nowrap"><?= money((int) $order['total']) ?> <span class="tiny muted">تومان</span></td>
                        <td class="nowrap">
                            <span class="pill <?= $order['payment_status'] === 'paid' ? 'pill-ok' : ($order['payment_status'] === 'failed' ? 'pill-danger' : 'pill-warn') ?>">
                                <?= $order['payment_status'] === 'paid' ? 'پرداخت‌شده' : ($order['payment_status'] === 'failed' ? 'ناموفق' : 'پرداخت‌نشده') ?>
                            </span>
                            <div class="tiny muted"><?= $order['payment_method'] === 'cod' ? 'پرداخت در محل' : 'آنلاین' ?></div>
                        </td>
                        <td><span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                        <td class="nowrap tiny muted"><?= e(jdate($order['created_at'], 'date_time')) ?></td>
                        <td><a class="btn btn-sm" href="<?= url('/admin/orders/' . (int) $order['id']) ?>">جزئیات</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php partial('pagination', ['pagination' => $pagination, 'query' => $query]); ?>
