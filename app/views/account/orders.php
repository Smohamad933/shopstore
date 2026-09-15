<?php
/**
 * فهرست سفارش‌های کاربر
 * @var array $orders
 */
?>
<div class="container">
    <h1 class="mb-2">سفارش‌های من</h1>

    <div class="account-layout">
        <?php partial('account-nav', ['user' => $user]); ?>

        <div>
            <?php if ($orders === []): ?>
                <?php partial('empty', [
                    'icon'        => 'package',
                    'title'       => 'هنوز سفارشی ثبت نکرده‌اید',
                    'text'        => 'با انتخاب محصولات مورد نیاز، اولین سفارش خود را ثبت کنید.',
                    'actionLabel' => 'مشاهده محصولات',
                    'actionUrl'   => url('/discounts'),
                ]); ?>
            <?php else: ?>
                <div class="table-card">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>کد سفارش</th>
                                <th>تاریخ</th>
                                <th>مبلغ کل</th>
                                <th>پرداخت</th>
                                <th>وضعیت</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="nowrap strong"><?= e($order['code']) ?></td>
                                    <td class="nowrap small muted"><?= e(jdate($order['created_at'], 'date_time')) ?></td>
                                    <td class="nowrap"><?= money((int) $order['total']) ?> <span class="tiny muted">تومان</span></td>
                                    <td>
                                        <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-new' : 'badge-warn' ?>">
                                            <?= $order['payment_status'] === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' ?>
                                        </span>
                                    </td>
                                    <td><span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                                    <td class="nowrap">
                                        <a class="btn btn-sm btn-ghost" href="<?= url('/account/orders/' . $order['code']) ?>">جزئیات</a>
                                        <?php if ($order['payment_status'] !== 'paid' && $order['payment_method'] === 'online'): ?>
                                            <a class="btn btn-sm btn-primary" href="<?= url('/payment/' . $order['code']) ?>">پرداخت</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
