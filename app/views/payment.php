<?php
/**
 * صفحه درگاه پرداخت (شبیه‌سازی‌شده برای پیش‌نمایش)
 * در حالت واقعی، این صفحه با اتصال به درگاه بانکی (زرین‌پال/آیدی‌پی) جایگزین می‌شود.
 * @var array $order
 * @var string $gateway
 */
?>
<div class="container container-narrow">
    <div class="surface pad-lg" style="margin-block:28px;">
        <div class="row-between wrap mb-2">
            <div>
                <h1 style="font-size:1.3rem; margin-bottom:2px;">درگاه پرداخت امن</h1>
                <p class="muted small mb-0">
                    کد سفارش: <b><?= e($order['code']) ?></b> — درگاه فعال: <?= e($gateway === 'zarinpal' ? 'زرین‌پال' : 'شبیه‌ساز پرداخت (حالت پیش‌نمایش)') ?>
                </p>
            </div>
            <span class="badge badge-warn">حالت آزمایشی</span>
        </div>

        <div class="alert alert-info">
            <?= icon('info') ?>
            <span>
                این صفحه شبیه‌ساز درگاه بانکی است تا کل مسیر خرید قابل تست باشد. در سایت واقعی، کاربر به
                درگاه بانک هدایت می‌شود و پس از پرداخت با پارامترهای Authority/Status برمی‌گردد.
            </span>
        </div>

        <div class="grid" style="grid-template-columns: repeat(2, minmax(0,1fr)); gap:14px; margin-block:18px;">
            <div class="surface pad">
                <div class="tiny muted">مبلغ قابل پرداخت</div>
                <div class="strong" style="font-size:1.3rem;"><?= money((int) $order['total']) ?> <span class="small muted">تومان</span></div>
                <div class="tiny muted mt-1"><?= e(amount_in_words((int) $order['total'])) ?></div>
            </div>
            <div class="surface pad">
                <div class="tiny muted">پذیرنده</div>
                <div class="strong"><?= e(setting('site_name')) ?></div>
                <div class="tiny muted mt-1">شناسه پرداخت: <?= fa_digits((string) $order['id']) ?></div>
            </div>
        </div>

        <table class="spec-table mb-2">
            <tbody>
            <tr><th>گیرنده</th><td><?= e($order['customer_name']) ?> — <?= e($order['customer_phone']) ?></td></tr>
            <tr><th>نشانی</th><td><?= e($order['province']) ?>، <?= e($order['city']) ?>، <?= e($order['address']) ?></td></tr>
            <tr><th>تعداد اقلام</th><td><?= fa_digits((int) $order['items_count']) ?> عدد</td></tr>
            </tbody>
        </table>

        <form method="post" action="<?= url('/payment/' . $order['code']) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="result" value="success">
            <button class="btn btn-primary btn-block btn-lg" type="submit">پرداخت <?= money((int) $order['total']) ?> تومان</button>
        </form>

        <form method="post" action="<?= url('/payment/' . $order['code']) ?>" class="mt-1">
            <?= csrf_field() ?>
            <input type="hidden" name="result" value="cancel">
            <button class="btn btn-ghost btn-block" type="submit">انصراف از پرداخت</button>
        </form>
    </div>
</div>
