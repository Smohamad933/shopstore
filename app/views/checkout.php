<?php
/**
 * فرم تکمیل سفارش
 * @var array $totals
 * @var array $user
 * @var array $addresses
 * @var array $shippingMethods
 * @var array $provinces
 */
$defaultAddress = $addresses[0] ?? null;
?>
<div class="container">
    <?php partial('breadcrumb', ['items' => [
        ['label' => 'سبد خرید', 'url' => url('/cart')],
        ['label' => 'تکمیل سفارش', 'url' => null],
    ]]); ?>

    <div class="steps">
        <span class="step is-done"><b><?= icon('check') ?></b> سبد خرید</span>
        <span class="step is-active"><b>۲</b> اطلاعات ارسال و پرداخت</span>
        <span class="step"><b>۳</b> پرداخت</span>
        <span class="step"><b>۴</b> تأیید سفارش</span>
    </div>

    <form method="post" action="<?= url('/checkout') ?>">
        <?= csrf_field() ?>
        <div class="checkout-layout">
            <div>
                <div class="surface pad-lg mb-2">
                    <h3>اطلاعات گیرنده</h3>
                    <div class="form-grid">
                        <div class="field">
                            <label class="label" for="customer_name">نام و نام خانوادگی <span class="req">*</span></label>
                            <input class="input" id="customer_name" type="text" name="customer_name" required
                                   value="<?= e(old('customer_name', $user['name'] ?? '')) ?>">
                        </div>
                        <div class="field">
                            <label class="label" for="customer_phone">شماره موبایل <span class="req">*</span></label>
                            <input class="input" id="customer_phone" type="tel" name="customer_phone" required inputmode="numeric"
                                   placeholder="09xxxxxxxxx" value="<?= e(old('customer_phone', $user['phone'] ?? '')) ?>">
                        </div>
                        <div class="field field-full">
                            <label class="label" for="customer_email">ایمیل (اختیاری — برای پیگیری سفارش)</label>
                            <input class="input" id="customer_email" type="email" name="customer_email"
                                   value="<?= e(old('customer_email', $user['email'] ?? '')) ?>">
                        </div>
                    </div>
                </div>

                <?php if ($addresses !== []): ?>
                    <div class="surface pad-lg mb-2">
                        <h3>آدرس‌های ذخیره‌شده</h3>
                        <?php foreach ($addresses as $index => $address): ?>
                            <label class="option-card <?= $index === 0 ? 'is-checked' : '' ?>"
                                   data-address-option
                                   data-receiver="<?= e($address['receiver']) ?>"
                                   data-phone="<?= e($address['phone']) ?>"
                                   data-province="<?= e($address['province']) ?>"
                                   data-city="<?= e($address['city']) ?>"
                                   data-address="<?= e($address['address']) ?>"
                                   data-postal="<?= e($address['postal_code']) ?>">
                                <input type="radio" name="saved_address" value="<?= (int) $address['id'] ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                <span>
                                    <b><?= e($address['title'] ?: 'آدرس') ?> — <?= e($address['receiver']) ?></b>
                                    <span><?= e($address['province']) ?>، <?= e($address['city']) ?>، <?= e($address['address']) ?></span>
                                    <span><?= e($address['phone']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="surface pad-lg mb-2">
                    <h3>نشانی تحویل</h3>
                    <div class="form-grid">
                        <div class="field">
                            <label class="label" for="province">استان <span class="req">*</span></label>
                            <select class="select" id="province" name="province" required>
                                <option value="">انتخاب کنید…</option>
                                <?php foreach ($provinces as $province): ?>
                                    <option value="<?= e($province) ?>" <?= old('province', $defaultAddress['province'] ?? '') === $province ? 'selected' : '' ?>><?= e($province) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="city">شهر <span class="req">*</span></label>
                            <input class="input" id="city" type="text" name="city" required value="<?= e(old('city', $defaultAddress['city'] ?? '')) ?>">
                        </div>
                        <div class="field field-full">
                            <label class="label" for="address">نشانی کامل <span class="req">*</span></label>
                            <textarea class="textarea" id="address" name="address" required style="min-height:90px;"
                                      placeholder="خیابان، کوچه، پلاک، واحد"><?= e(old('address', $defaultAddress['address'] ?? '')) ?></textarea>
                        </div>
                        <div class="field">
                            <label class="label" for="postal_code">کد پستی</label>
                            <input class="input" id="postal_code" type="text" name="postal_code" inputmode="numeric"
                                   value="<?= e(old('postal_code', $defaultAddress['postal_code'] ?? '')) ?>">
                        </div>
                        <div class="field">
                            <label class="label" for="note">توضیحات سفارش (اختیاری)</label>
                            <input class="input" id="note" type="text" name="note" placeholder="مثلاً: قبل از ارسال تماس بگیرید"
                                   value="<?= e(old('note')) ?>">
                        </div>
                    </div>
                </div>

                <div class="surface pad-lg mb-2">
                    <h3>روش ارسال</h3>
                    <?php foreach ($shippingMethods as $key => $method): ?>
                        <label class="option-card <?= $key === 'post' ? 'is-checked' : '' ?>" data-option-card>
                            <input type="radio" name="shipping_method" value="<?= e($key) ?>" <?= $key === 'post' ? 'checked' : '' ?>>
                            <span>
                                <b><?= e($method['label']) ?></b>
                                <span><?= e($method['note']) ?></span>
                            </span>
                            <span class="muted small" style="margin-inline-start:auto;">
                                <?= $key === 'pickup' ? 'رایگان' : (shipping_cost($totals['payable'], $key) === 0 ? 'رایگان' : money(shipping_cost($totals['payable'], $key)) . ' تومان') ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="surface pad-lg">
                    <h3>روش پرداخت</h3>
                    <?php if (setting('online_enabled') === '1'): ?>
                        <label class="option-card is-checked" data-option-card>
                            <input type="radio" name="payment_method" value="online" checked>
                            <span>
                                <b>پرداخت آنلاین (درگاه بانکی)</b>
                                <span>انتقال به درگاه امن و پرداخت با کارت‌های شتاب</span>
                            </span>
                        </label>
                    <?php endif; ?>
                    <?php if (setting('cod_enabled') === '1'): ?>
                        <label class="option-card" data-option-card>
                            <input type="radio" name="payment_method" value="cod" <?= setting('online_enabled') === '1' ? '' : 'checked' ?>>
                            <span>
                                <b>پرداخت در محل (فقط شهر تهران)</b>
                                <span>پرداخت نقدی یا کارتخوان هنگام تحویل کالا</span>
                            </span>
                        </label>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="summary-card">
                <h3 style="font-size:1rem;">خلاصه سفارش</h3>
                <?php foreach ($totals['items'] as $item): ?>
                    <div class="row-between" style="padding-block:8px; border-bottom:1px solid var(--line-2);">
                        <span class="small grow">
                            <?= e(mb_substr($item['product']['name'], 0, 42)) ?><?= mb_strlen($item['product']['name']) > 42 ? '…' : '' ?>
                            <span class="muted tiny">× <?= fa_digits($item['qty']) ?></span>
                        </span>
                        <span class="small strong nowrap"><?= money($item['line_total']) ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row">
                    <span class="muted">مبلغ کالاها</span>
                    <span class="strong"><?= money($totals['subtotal']) ?></span>
                </div>
                <?php if ($totals['discount'] > 0): ?>
                    <div class="summary-row">
                        <span class="muted">تخفیف <?= $totals['coupon'] ? '«' . e($totals['coupon']['code']) . '»' : '' ?></span>
                        <span class="strong" style="color:var(--sale)">− <?= money($totals['discount']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span class="muted">هزینه ارسال</span>
                    <span class="strong"><?= money($totals['shipping'], true) ?></span>
                </div>
                <div class="summary-row total">
                    <span>مبلغ قابل پرداخت</span>
                    <span><?= money($totals['total']) ?> <span class="tiny muted">تومان</span></span>
                </div>

                <button class="btn btn-primary btn-block btn-lg mt-2" type="submit">ثبت سفارش و پرداخت</button>
                <p class="tiny muted center mt-1 mb-0">
                    با ثبت سفارش، <a href="<?= url('/terms') ?>">قوانین و مقررات</a> فروشگاه را می‌پذیرید.
                </p>
            </aside>
        </div>
    </form>
</div>

<script>
    // پر کردن خودکار فرم از آدرس ذخیره‌شده
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-address-option]').forEach(function (option) {
            option.addEventListener('click', function () {
                const set = function (id, value) {
                    const field = document.getElementById(id);
                    if (field && value) field.value = value;
                };
                set('province', option.dataset.province);
                set('city', option.dataset.city);
                set('address', option.dataset.address);
                set('postal_code', option.dataset.postal);
                // انتخاب رادیو
                const radio = option.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
                document.querySelectorAll('[data-address-option]').forEach(function (item) {
                    item.classList.toggle('is-checked', item === option);
                });
            });
        });

        document.querySelectorAll('[data-option-card]').forEach(function (card) {
            card.addEventListener('click', function () {
                const name = card.querySelector('input') ? card.querySelector('input').name : '';
                document.querySelectorAll('[data-option-card]').forEach(function (item) {
                    if (item.querySelector('input') && item.querySelector('input').name === name) {
                        item.classList.toggle('is-checked', item === card);
                    }
                });
            });
        });
    });
</script>
