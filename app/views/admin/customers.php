<?php
/**
 * فهرست مشتریان
 * @var array $customers
 * @var string $term
 */
?>
<form class="toolbar-admin" method="get" action="<?= url('/admin/customers') ?>">
    <input class="input" type="search" name="q" placeholder="نام، ایمیل یا موبایل مشتری" value="<?= e($term) ?>">
    <button class="btn btn-sm" type="submit">جست‌وجو</button>
    <a class="btn btn-sm btn-ghost" href="<?= url('/admin/customers') ?>">حذف فیلتر</a>
    <span class="muted small" style="margin-inline-start:auto;"><?= fa_digits(count($customers)) ?> مشتری</span>
</form>

<div class="card">
    <?php if ($customers === []): ?>
        <div class="empty-admin">مشتری‌ای با این مشخصات پیدا نشد.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>مشتری</th>
                    <th>موبایل</th>
                    <th>تعداد سفارش</th>
                    <th>مجموع خرید</th>
                    <th>تاریخ عضویت</th>
                    <th>آخرین ورود</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <div class="row">
                                <span class="avatar" style="width:34px; height:34px; font-size:.8rem;"><?= e(mb_substr($customer['name'], 0, 1)) ?></span>
                                <div>
                                    <div class="small strong"><?= e($customer['name']) ?></div>
                                    <div class="tiny muted" style="direction:ltr; text-align:right;"><?= e($customer['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="nowrap"><?= $customer['phone'] ? fa_digits((string) $customer['phone']) : '—' ?></td>
                        <td><?= fa_digits((int) $customer['orders_count']) ?></td>
                        <td class="nowrap"><?= money((int) $customer['total_spent']) ?> <span class="tiny muted">تومان</span></td>
                        <td class="nowrap tiny muted"><?= e(jdate($customer['created_at'])) ?></td>
                        <td class="nowrap tiny muted"><?= $customer['last_login_at'] ? e(jdate($customer['last_login_at'], 'date_time')) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
