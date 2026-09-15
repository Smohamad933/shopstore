<?php
/**
 * جزئیات سفارش در پنل مدیریت
 * @var array $order
 * @var array $customerOrders
 */
?>
<div class="row-between wrap mb-2">
    <a class="btn btn-sm btn-ghost" href="<?= url('/admin/orders') ?>"><?= icon('chevron-r') ?> بازگشت به سفارش‌ها</a>
    <div class="row">
        <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
        <span class="pill <?= $order['payment_status'] === 'paid' ? 'pill-ok' : 'pill-warn' ?>">
            <?= $order['payment_status'] === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' ?>
        </span>
        <a class="btn btn-sm" href="<?= url('/order/' . $order['code']) ?>" target="_blank"><?= icon('eye') ?> نمای مشتری</a>
    </div>
</div>

<div class="admin-grid">
    <div>
        <div class="card mb-2">
            <div class="card-head">
                <h3>اقلام سفارش</h3>
                <span class="muted small"><?= fa_digits((int) $order['items_count']) ?> قلم</span>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr><th>کالا</th><th>قیمت واحد</th><th>تعداد</th><th>جمع</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <div class="row">
                                    <img class="thumb-xs" src="<?= e(product_image($item['image'])) ?>" alt="">
                                    <div>
                                        <div class="small strong"><?= e($item['name']) ?></div>
                                        <div class="tiny muted"><?= e($item['brand']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="nowrap"><?= money((int) $item['price']) ?></td>
                            <td><?= fa_digits((int) $item['qty']) ?></td>
                            <td class="nowrap strong"><?= money((int) $item['price'] * (int) $item['qty']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <div class="row-between small"><span class="muted">مبلغ کالاها</span><span><?= money((int) $order['subtotal']) ?></span></div>
                <?php if ((int) $order['discount'] > 0): ?>
                    <div class="row-between small"><span class="muted">تخفیف <?= $order['coupon_code'] ? '(' . e($order['coupon_code']) . ')' : '' ?></span><span>− <?= money((int) $order['discount']) ?></span></div>
                <?php endif; ?>
                <div class="row-between small"><span class="muted">هزینه ارسال</span><span><?= money((int) $order['shipping'], true) ?></span></div>
                <div class="row-between strong" style="border-top:1px solid var(--line-2); padding-top:10px; margin-top:8px;">
                    <span>مبلغ کل</span><span><?= money((int) $order['total']) ?> تومان</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h3>اطلاعات ارسال</h3></div>
            <div class="card-body small">
                <div class="row-between mb-1"><span class="muted">گیرنده</span><span><?= e($order['customer_name']) ?></span></div>
                <div class="row-between mb-1"><span class="muted">موبایل</span><span><?= fa_digits((string) $order['customer_phone']) ?></span></div>
                <?php if ($order['customer_email']): ?>
                    <div class="row-between mb-1"><span class="muted">ایمیل</span><span><?= e($order['customer_email']) ?></span></div>
                <?php endif; ?>
                <div class="row-between mb-1"><span class="muted">استان / شهر</span><span><?= e($order['province']) ?> / <?= e($order['city']) ?></span></div>
                <div class="row-between mb-1"><span class="muted">نشانی</span><span style="max-width:60%; text-align:left;"><?= e($order['address']) ?></span></div>
                <?php if ($order['postal_code']): ?>
                    <div class="row-between mb-1"><span class="muted">کد پستی</span><span><?= fa_digits((string) $order['postal_code']) ?></span></div>
                <?php endif; ?>
                <div class="row-between mb-1"><span class="muted">روش ارسال</span><span><?= e(shipping_methods()[$order['shipping_method']]['label'] ?? '—') ?></span></div>
                <div class="row-between mb-1"><span class="muted">روش پرداخت</span><span><?= $order['payment_method'] === 'cod' ? 'پرداخت در محل' : 'آنلاین' ?></span></div>
                <?php if ($order['payment_ref']): ?>
                    <div class="row-between mb-1"><span class="muted">کد پیگیری پرداخت</span><span><?= fa_digits((string) $order['payment_ref']) ?></span></div>
                <?php endif; ?>
                <div class="row-between mb-1"><span class="muted">تاریخ ثبت</span><span><?= e(jdate($order['created_at'], 'date_time')) ?></span></div>
                <?php if ($order['note']): ?>
                    <div class="mt-2"><span class="muted">یادداشت مشتری:</span><br><?= e($order['note']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="card mb-2">
            <div class="card-head"><h3>بروزرسانی وضعیت</h3></div>
            <div class="card-body">
                <form class="admin-form" method="post" action="<?= url('/admin/orders/' . (int) $order['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="label" for="status">وضعیت سفارش</label>
                        <select class="select" id="status" name="status">
                            <?php foreach (ORDER_STATUSES as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="tracking_code">کد رهگیری مرسوله</label>
                        <input class="input" id="tracking_code" type="text" name="tracking_code" value="<?= e($order['tracking_code']) ?>">
                    </div>
                    <div class="field">
                        <label class="label" for="admin_note">یادداشت داخلی</label>
                        <textarea class="textarea" id="admin_note" name="admin_note" style="min-height:80px;"><?= e($order['admin_note']) ?></textarea>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit">ذخیره وضعیت</button>
                </form>
            </div>
        </div>

        <?php if ($customerOrders !== []): ?>
            <div class="card">
                <div class="card-head"><h3>سایر سفارش‌های این مشتری</h3></div>
                <div class="card-body">
                    <?php foreach ($customerOrders as $row): ?>
                        <div class="row-between" style="border-bottom:1px solid var(--line-2); padding:8px 0;">
                            <a class="small" href="<?= url('/admin/orders?q=' . urlencode($row['code'])) ?>"><?= e($row['code']) ?></a>
                            <span class="tiny muted"><?= money((int) $row['total']) ?></span>
                            <span class="status <?= e(order_status_class($row['status'])) ?>"><?= e(order_status_label($row['status'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
