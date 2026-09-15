<?php
/**
 * داشبورد مدیریت
 * @var array $stats
 * @var array $series
 * @var int $maxAmount
 * @var array $latestOrders
 * @var array $lowStock
 * @var array $topProducts
 * @var array $messages
 */
?>
<div class="admin-stats">
    <div class="stat-card">
        <div class="label"><?= icon('chart') ?> فروش این ماه</div>
        <div class="value"><?= money($stats['revenue_month']) ?> <span class="small muted">تومان</span></div>
        <div class="delta">مجموع کل: <?= toman($stats['revenue_total']) ?></div>
    </div>
    <div class="stat-card">
        <div class="label"><?= icon('package') ?> سفارش‌های امروز</div>
        <div class="value"><?= fa_digits($stats['orders_today']) ?></div>
        <div class="delta">کل سفارش‌ها: <?= fa_digits($stats['orders_total']) ?> • در انتظار: <?= fa_digits($stats['pending_orders']) ?></div>
    </div>
    <div class="stat-card">
        <div class="label"><?= icon('tag') ?> محصولات</div>
        <div class="value"><?= fa_digits($stats['products']) ?></div>
        <div class="delta"><?= fa_digits($stats['low_stock']) ?> کالا با موجودی کم (۳ عدد یا کمتر)</div>
    </div>
    <div class="stat-card">
        <div class="label"><?= icon('user') ?> مشتریان</div>
        <div class="value"><?= fa_digits($stats['customers']) ?></div>
        <div class="delta"><?= fa_digits($stats['messages_new']) ?> پیام خوانده‌نشده</div>
    </div>
</div>

<div class="admin-grid">
    <div class="card">
        <div class="card-head">
            <h3>فروش ۷ روز اخیر</h3>
            <span class="muted small">بر اساس سفارش‌های پرداخت‌شده</span>
        </div>
        <div class="card-body">
            <div class="chart">
                <?php foreach ($series as $row): ?>
                    <?php $height = max(3, (int) round(($row['amount'] / $maxAmount) * 100)); ?>
                    <div class="bar-wrap" title="<?= e($row['label']) ?>: <?= toman($row['amount']) ?>">
                        <?php if ($row['amount'] > 0): ?>
                            <span class="bar-value"><?= money($row['amount']) ?></span>
                        <?php endif; ?>
                        <div class="bar" style="height: <?= $height ?>%;"></div>
                        <span class="bar-label"><?= e($row['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>کالاهای کم‌موجود</h3>
            <a class="btn btn-sm btn-ghost" href="<?= url('/admin/products?stock=low') ?>">مدیریت</a>
        </div>
        <?php if ($lowStock === []): ?>
            <div class="empty-admin">موجودی همه کالاها مناسب است.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <tbody>
                    <?php foreach ($lowStock as $product): ?>
                        <tr>
                            <td><img class="thumb-xs" src="<?= e(product_image($product['image'])) ?>" alt=""></td>
                            <td>
                                <a class="small strong" href="<?= url('/admin/products/' . (int) $product['id'] . '/edit') ?>"><?= e(mb_substr($product['name'], 0, 38)) ?></a>
                                <div class="tiny muted"><?= e($product['brand']) ?></div>
                            </td>
                            <td class="nowrap">
                                <span class="pill <?= (int) $product['stock'] === 0 ? 'pill-danger' : 'pill-warn' ?>">
                                    <?= fa_digits((int) $product['stock']) ?> عدد
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-grid mt-3">
    <div class="card">
        <div class="card-head">
            <h3>آخرین سفارش‌ها</h3>
            <a class="btn btn-sm btn-ghost" href="<?= url('/admin/orders') ?>">همه سفارش‌ها</a>
        </div>
        <?php if ($latestOrders === []): ?>
            <div class="empty-admin">هنوز سفارشی ثبت نشده است.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr><th>کد</th><th>مشتری</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($latestOrders as $order): ?>
                        <tr>
                            <td class="nowrap strong"><?= e($order['code']) ?></td>
                            <td>
                                <?= e($order['customer_name']) ?>
                                <div class="tiny muted"><?= fa_digits((string) $order['customer_phone']) ?></div>
                            </td>
                            <td class="nowrap"><?= money((int) $order['total']) ?></td>
                            <td><span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                            <td class="nowrap tiny muted"><?= e(jdate($order['created_at'], 'date_time')) ?></td>
                            <td><a class="btn btn-sm" href="<?= url('/admin/orders/' . (int) $order['id']) ?>">مشاهده</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head"><h3>پرفروش‌ترین‌ها</h3></div>
        <?php if ($topProducts === []): ?>
            <div class="empty-admin">داده‌ای موجود نیست.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <tbody>
                    <?php foreach ($topProducts as $product): ?>
                        <tr>
                            <td><img class="thumb-xs" src="<?= e(product_image($product['image'])) ?>" alt=""></td>
                            <td><div class="small"><?= e(mb_substr($product['name'], 0, 40)) ?></div></td>
                            <td class="nowrap small"><?= fa_digits((int) ($product['sold'] ?? 0)) ?> فروش</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($messages !== []): ?>
    <div class="card mt-3">
        <div class="card-head">
            <h3>آخرین پیام‌های مشتریان</h3>
            <a class="btn btn-sm btn-ghost" href="<?= url('/admin/messages') ?>">همه پیام‌ها</a>
        </div>
        <div class="card-body">
            <?php foreach ($messages as $message): ?>
                <div style="border-bottom:1px solid var(--line-2); padding:10px 0;">
                    <div class="row-between">
                        <b class="small"><?= e($message['name']) ?> — <?= e($message['subject']) ?></b>
                        <span class="tiny muted"><?= e(jdate($message['created_at'], 'date_time')) ?></span>
                    </div>
                    <p class="small muted mb-0"><?= e(mb_substr($message['message'], 0, 150)) ?>…</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
