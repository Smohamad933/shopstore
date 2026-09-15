<?php
/**
 * کدهای تخفیف
 * @var array $coupons
 */
?>
<div class="admin-grid">
    <div class="card">
        <div class="card-head">
            <h3>کدهای فعال</h3>
            <span class="muted small">کدها هنگام تکمیل سفارش قابل استفاده هستند</span>
        </div>
        <?php if ($coupons === []): ?>
            <div class="empty-admin">کد تخفیفی ثبت نشده است.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr><th>کد</th><th>مقدار</th><th>حداقل سفارش</th><th>استفاده‌شده</th><th>انقضا</th><th>وضعیت</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td class="strong" style="direction:ltr; text-align:right;"><?= e($coupon['code']) ?></td>
                            <td>
                                <?= $coupon['type'] === 'percent'
                                    ? fa_digits((int) $coupon['amount']) . '٪'
                                    : money((int) $coupon['amount']) . ' تومان' ?>
                            </td>
                            <td class="nowrap"><?= (int) $coupon['min_order'] > 0 ? toman((int) $coupon['min_order']) : '—' ?></td>
                            <td>
                                <?= fa_digits((int) $coupon['used_count']) ?>
                                <?= (int) $coupon['max_uses'] > 0 ? '/ ' . fa_digits((int) $coupon['max_uses']) : '' ?>
                            </td>
                            <td class="nowrap tiny muted"><?= $coupon['expires_at'] ? e(jdate($coupon['expires_at'])) : 'بدون انقضا' ?></td>
                            <td>
                                <?php $expired = $coupon['expires_at'] && strtotime((string) $coupon['expires_at']) < time(); ?>
                                <span class="pill <?= $expired ? 'pill-danger' : ((int) $coupon['is_active'] === 1 ? 'pill-ok' : 'pill-warn') ?>">
                                    <?= $expired ? 'منقضی' : ((int) $coupon['is_active'] === 1 ? 'فعال' : 'غیرفعال') ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" action="<?= url('/admin/coupons/' . (int) $coupon['id'] . '/delete') ?>"
                                      data-confirm="کد «<?= e($coupon['code']) ?>» حذف شود؟">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger" type="submit"><?= icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head"><h3>ایجاد کد تخفیف</h3></div>
        <div class="card-body">
            <form class="admin-form" method="post" action="<?= url('/admin/coupons/save') ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label class="label" for="code">کد <span class="req">*</span></label>
                    <input class="input" id="code" type="text" name="code" required placeholder="NOWRUZ25" style="direction:ltr; text-align:left;">
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="type">نوع تخفیف</label>
                        <select class="select" id="type" name="type">
                            <option value="percent">درصدی</option>
                            <option value="fixed">مبلغ ثابت</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="amount">مقدار <span class="req">*</span></label>
                        <input class="input" id="amount" type="number" name="amount" required min="1">
                        <div class="hint">درصد (۱ تا ۹۰) یا مبلغ به تومان</div>
                    </div>
                    <div class="field">
                        <label class="label" for="min_order">حداقل مبلغ سفارش</label>
                        <input class="input" id="min_order" type="number" name="min_order" min="0" step="10000" value="0">
                    </div>
                    <div class="field">
                        <label class="label" for="max_uses">حداکثر تعداد استفاده</label>
                        <input class="input" id="max_uses" type="number" name="max_uses" min="0" value="0">
                        <div class="hint">صفر = بدون محدودیت</div>
                    </div>
                </div>
                <div class="field">
                    <label class="label" for="expires_at">تاریخ انقضا</label>
                    <input class="input" id="expires_at" type="date" name="expires_at">
                    <div class="hint">خالی بگذارید تا کد بدون انقضا باشد.</div>
                </div>
                <label class="checkbox mb-2"><input type="checkbox" name="is_active" value="1" checked><span>کد فعال باشد</span></label>
                <button class="btn btn-primary btn-block" type="submit">ایجاد کد تخفیف</button>
            </form>
        </div>
    </div>
</div>
