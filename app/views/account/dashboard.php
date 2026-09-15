<?php
/**
 * پیشخوان کاربر
 * @var array $orders
 * @var array $addresses
 * @var array $stats
 */
?>
<div class="container">
    <h1 class="mb-2">پنل کاربری</h1>

    <div class="account-layout">
        <?php partial('account-nav', ['user' => $user]); ?>

        <div>
            <div class="grid" style="grid-template-columns: repeat(3, minmax(0,1fr)); gap:12px; margin-bottom:16px;">
                <div class="surface pad">
                    <div class="tiny muted">تعداد سفارش‌ها</div>
                    <div class="strong" style="font-size:1.3rem;"><?= fa_digits($stats['orders']) ?></div>
                </div>
                <div class="surface pad">
                    <div class="tiny muted">در حال پیگیری</div>
                    <div class="strong" style="font-size:1.3rem;"><?= fa_digits($stats['pending']) ?></div>
                </div>
                <div class="surface pad">
                    <div class="tiny muted">مجموع خرید</div>
                    <div class="strong" style="font-size:1.3rem;"><?= money($stats['spent']) ?> <span class="tiny muted">تومان</span></div>
                </div>
            </div>

            <div class="surface pad-lg mb-2">
                <div class="row-between mb-2">
                    <h3 style="margin:0; font-size:1rem;">آخرین سفارش‌ها</h3>
                    <a class="link small" href="<?= url('/account/orders') ?>">همه سفارش‌ها</a>
                </div>

                <?php if ($orders === []): ?>
                    <p class="muted small mb-0">هنوز سفارشی ثبت نکرده‌اید.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                            <tr><th>کد سفارش</th><th>تاریخ</th><th>اقلام</th><th>مبلغ</th><th>وضعیت</th><th></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="nowrap"><?= e($order['code']) ?></td>
                                    <td class="nowrap small"><?= e(jdate($order['created_at'])) ?></td>
                                    <td><?= fa_digits((int) $order['items_count']) ?></td>
                                    <td class="nowrap"><?= money((int) $order['total']) ?></td>
                                    <td><span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                                    <td><a class="btn btn-sm btn-ghost" href="<?= url('/account/orders/' . $order['code']) ?>">جزئیات</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="surface pad-lg">
                <div class="row-between mb-2">
                    <h3 style="margin:0; font-size:1rem;">آدرس‌های من</h3>
                    <a class="link small" href="<?= url('/account/addresses') ?>">مدیریت آدرس‌ها</a>
                </div>
                <?php if ($addresses === []): ?>
                    <p class="muted small mb-0">هنوز آدرسی ثبت نشده است.</p>
                <?php else: ?>
                    <?php foreach (array_slice($addresses, 0, 2) as $address): ?>
                        <div class="row-between" style="border:1px solid var(--line-2); border-radius:var(--radius-sm); padding:10px 12px; margin-bottom:8px;">
                            <div>
                                <b class="small"><?= e($address['title'] ?: 'آدرس') ?> — <?= e($address['receiver']) ?></b>
                                <div class="tiny muted"><?= e($address['province']) ?>، <?= e($address['city']) ?>، <?= e($address['address']) ?></div>
                            </div>
                            <?php if ((int) $address['is_default'] === 1): ?>
                                <span class="badge badge-new">پیش‌فرض</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
