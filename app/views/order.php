<?php
/**
 * جزئیات / نتیجه سفارش
 * @var array $order
 * @var bool $isOwner
 * @var bool $showDetails
 */
$statusOrder = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
$currentIndex = array_search($order['status'], $statusOrder, true);
$currentIndex = $currentIndex === false ? 0 : $currentIndex;
$isPaid = $order['payment_status'] === 'paid';
?>
<div class="container">
    <?php partial('breadcrumb', ['items' => array_filter([
        is_logged_in() ? ['label' => 'حساب کاربری', 'url' => url('/account')] : null,
        ['label' => 'سفارش ' . $order['code'], 'url' => null],
    ])]); ?>

    <div class="surface pad-lg mb-2">
        <div class="row-between wrap">
            <div class="row">
                <span class="avatar" style="background:<?= $isPaid ? 'var(--ok-soft)' : 'var(--warn-soft)' ?>; color:<?= $isPaid ? 'var(--ok)' : 'var(--warn)' ?>; width:52px; height:52px;">
                    <?= icon($isPaid ? 'check' : 'clock') ?>
                </span>
                <div>
                    <h1 style="font-size:1.25rem; margin-bottom:2px;">
                        <?php if ($isPaid): ?>
                            سفارش شما با موفقیت ثبت شد
                        <?php elseif ($order['payment_status'] === 'failed'): ?>
                            پرداخت انجام نشد
                        <?php else: ?>
                            سفارش شما در انتظار پرداخت است
                        <?php endif; ?>
                    </h1>
                    <p class="muted small mb-0">
                        کد سفارش: <b data-copy-target><?= e($order['code']) ?></b>
                        <button class="btn btn-sm btn-ghost" type="button" data-copy="<?= e($order['code']) ?>">کپی کد</button>
                        • تاریخ ثبت: <?= e(jdate($order['created_at'], 'date_time')) ?>
                    </p>
                </div>
            </div>
            <div class="row wrap">
                <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                <span class="badge <?= $isPaid ? 'badge-new' : 'badge-warn' ?>">
                    <?= $isPaid ? 'پرداخت‌شده' : ($order['payment_method'] === 'cod' ? 'پرداخت در محل' : 'پرداخت‌نشده') ?>
                </span>
                <button class="btn btn-sm btn-ghost no-print" type="button" onclick="window.print()">چاپ فاکتور</button>
            </div>
        </div>

        <?php if (in_array($order['status'], $statusOrder, true)): ?>
            <div class="order-track">
                <?php foreach ($statusOrder as $index => $status): ?>
                    <div class="node <?= $index <= $currentIndex ? 'is-done' : '' ?>"><?= e(order_status_label($status)) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$isPaid && $order['payment_method'] === 'online' && $order['status'] !== 'canceled'): ?>
            <div class="alert alert-warn">
                <?= icon('info') ?>
                <span>پرداخت این سفارش کامل نشده است. برای نهایی شدن سفارش، پرداخت را تکمیل کنید.</span>
                <a class="btn btn-sm btn-primary" href="<?= url('/payment/' . $order['code']) ?>">پرداخت مجدد</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid" style="grid-template-columns: minmax(0,1fr) 340px; gap:20px; align-items:start;">
        <div class="table-card">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>کالا</th>
                        <th>قیمت واحد</th>
                        <th>تعداد</th>
                        <th>جمع</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <div class="row">
                                    <img class="thumb-sm" src="<?= e(product_image($item['image'])) ?>" alt="">
                                    <div>
                                        <a class="small strong" href="<?= url('/product/' . (int) $item['product_id'] . '-' . $item['slug']) ?>"><?= e($item['name']) ?></a>
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
        </div>

        <aside class="summary-card">
            <h3 style="font-size:1rem;">خلاصه پرداخت</h3>
            <div class="summary-row"><span class="muted">مبلغ کالاها</span><span class="strong"><?= money((int) $order['subtotal']) ?></span></div>
            <?php if ((int) $order['discount'] > 0): ?>
                <div class="summary-row">
                    <span class="muted">تخفیف <?= $order['coupon_code'] ? '«' . e($order['coupon_code']) . '»' : '' ?></span>
                    <span class="strong" style="color:var(--sale)">− <?= money((int) $order['discount']) ?></span>
                </div>
            <?php endif; ?>
            <div class="summary-row"><span class="muted">هزینه ارسال</span><span class="strong"><?= money((int) $order['shipping'], true) ?></span></div>
            <div class="summary-row total"><span>مبلغ کل</span><span><?= money((int) $order['total']) ?> <span class="tiny muted">تومان</span></span></div>

            <div class="mt-2" style="border-top:1px solid var(--line-2); padding-top:12px;">
                <div class="tiny muted">روش ارسال</div>
                <div class="small strong"><?= e(shipping_methods()[$order['shipping_method']]['label'] ?? 'ارسال عادی') ?></div>

                <div class="tiny muted mt-1">گیرنده</div>
                <div class="small"><?= e($order['customer_name']) ?> — <?= e($order['customer_phone']) ?></div>

                <div class="tiny muted mt-1">نشانی</div>
                <div class="small"><?= e($order['province']) ?>، <?= e($order['city']) ?><?= $order['postal_code'] ? ' — کد پستی ' . fa_digits($order['postal_code']) : '' ?></div>
                <div class="small muted"><?= e($order['address']) ?></div>

                <?php if ($order['payment_ref']): ?>
                    <div class="tiny muted mt-1">کد پیگیری پرداخت</div>
                    <div class="small"><?= fa_digits((string) $order['payment_ref']) ?></div>
                <?php endif; ?>

                <?php if ($order['tracking_code']): ?>
                    <div class="tiny muted mt-1">کد رهگیری مرسوله</div>
                    <div class="small"><?= e($order['tracking_code']) ?></div>
                <?php endif; ?>
            </div>

            <div class="row wrap mt-2 no-print">
                <a class="btn btn-sm" href="<?= url('/discounts') ?>">ادامه خرید</a>
                <a class="btn btn-sm btn-ghost" href="<?= url('/contact') ?>">پیگیری با پشتیبانی</a>
            </div>
        </aside>
    </div>

    <?php if (!is_logged_in() && $isPaid): ?>
        <div class="alert alert-info mt-2">
            <?= icon('info') ?>
            <span>
                برای پیگیری آسان‌تر سفارش‌ها، با همین ایمیل
                <a href="<?= url('/register') ?>">حساب کاربری بسازید</a> یا
                <a href="<?= url('/login') ?>">وارد شوید</a>.
            </span>
        </div>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                window.shopToast && window.shopToast('کد سفارش کپی شد.', 'success');
            } catch (error) {
                window.shopToast && window.shopToast('کپی انجام نشد؛ کد را دستی یادداشت کنید.', 'error');
            }
        });
    });
</script>
