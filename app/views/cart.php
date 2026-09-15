<?php
/**
 * سبد خرید
 * @var array $totals
 * @var array $suggestions
 */
?>
<div class="container">
    <?php partial('breadcrumb', ['items' => [['label' => 'سبد خرید', 'url' => null]]]); ?>
    <h1 class="mb-2">سبد خرید</h1>

    <?php if ($totals['items'] === []): ?>
        <?php partial('empty', [
            'icon'        => 'cart',
            'title'       => 'سبد خرید شما خالی است',
            'text'        => 'محصولات مورد نیاز پروژه‌تان را انتخاب کنید؛ می‌توانید با جست‌وجو یا از طریق دسته‌بندی‌ها شروع کنید.',
            'actionLabel' => 'شروع خرید',
            'actionUrl'   => url('/discounts'),
        ]); ?>
    <?php else: ?>
        <div class="cart-layout" data-cart-page>
            <div class="surface" style="padding: 4px 18px;">
                <?php foreach ($totals['items'] as $item): ?>
                    <?php $product = $item['product']; ?>
                    <div class="cart-item" data-cart-row="<?= (int) $product['id'] ?>">
                        <a class="thumb" href="<?= url(product_url($product)) ?>">
                            <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>">
                        </a>

                        <div class="grow">
                            <a href="<?= url(product_url($product)) ?>" class="strong small"><?= e($product['name']) ?></a>
                            <div class="tiny muted mt-1">برند: <?= e($product['brand']) ?> • موجودی انبار: <?= fa_digits((int) $product['stock']) ?></div>
                            <div class="row mt-1" style="gap:12px;">
                                <span class="small muted">قیمت واحد: <?= money($item['unit_price']) ?> تومان</span>
                                <button class="cart-remove" type="button" data-cart-remove>
                                    <?= icon('trash') ?> حذف
                                </button>
                            </div>
                        </div>

                        <div class="cart-qty" style="text-align:center;">
                            <div class="qty">
                                <button type="button" data-qty-minus aria-label="کاهش">−</button>
                                <input type="number" value="<?= (int) $item['qty'] ?>" min="1" max="<?= (int) $product['stock'] ?>" data-qty-input aria-label="تعداد">
                                <button type="button" data-qty-plus aria-label="افزایش">+</button>
                            </div>
                            <div class="small strong mt-1"><span data-line-total><?= money($item['line_total']) ?></span> <span class="tiny muted">تومان</span></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="row-between wrap" style="padding: 14px 0;">
                    <a class="btn btn-ghost" href="<?= url('/discounts') ?>"><?= icon('chevron-r') ?> ادامه خرید</a>
                    <span class="tiny muted">قیمت‌ها شامل مالیات بر ارزش افزوده است.</span>
                </div>
            </div>

            <aside class="summary-card">
                <h3 style="font-size:1rem;">جمع سفارش</h3>

                <?php if (($totals['free_shipping_remaining'] ?? 0) > 0): ?>
                    <div class="progress-note">
                        <?= toman($totals['free_shipping_remaining']) ?> تا ارسال رایگان فاصله دارید.
                    </div>
                <?php else: ?>
                    <div class="progress-note">🎉 ارسال این سفارش رایگان است.</div>
                <?php endif; ?>

                <div class="summary-row">
                    <span class="muted">مبلغ کالاها (<?= fa_digits($totals['count']) ?> عدد)</span>
                    <span class="strong"><span data-summary-subtotal><?= money($totals['subtotal']) ?></span> <span class="tiny muted">تومان</span></span>
                </div>

                <div class="summary-row <?= $totals['discount'] > 0 ? '' : 'hidden' ?>" data-discount-row>
                    <span class="muted">تخفیف <?= $totals['coupon'] ? '«' . e($totals['coupon']['code']) . '»' : '' ?></span>
                    <span class="strong" style="color:var(--sale)">− <span data-summary-discount><?= money($totals['discount']) ?></span></span>
                </div>

                <div class="summary-row">
                    <span class="muted">هزینه ارسال</span>
                    <span class="strong"><span data-summary-shipping><?= money($totals['shipping'], true) ?></span></span>
                </div>

                <div class="summary-row total">
                    <span>مبلغ قابل پرداخت</span>
                    <span><span data-summary-total><?= money($totals['total']) ?></span> <span class="tiny muted">تومان</span></span>
                </div>

                <form class="mt-2" data-coupon-form>
                    <label class="label" for="coupon">کد تخفیف دارید؟</label>
                    <div class="input-group">
                        <input class="input" id="coupon" type="text" name="code" placeholder="مثلاً WELCOME10"
                               value="<?= e($totals['coupon']['code'] ?? '') ?>">
                        <button class="btn" type="submit">اعمال</button>
                    </div>
                    <div class="hint">کدهای فعال نمونه: WELCOME10 (۱۰٪ تخفیف) و SAZEH500 (۵۰۰٬۰۰۰ تومان تخفیف)</div>
                </form>

                <a class="btn btn-primary btn-block btn-lg mt-2" href="<?= url('/checkout') ?>">تکمیل سفارش</a>
                <p class="tiny muted center mt-1 mb-0">پرداخت امن از طریق درگاه بانکی یا پرداخت در محل</p>
            </aside>
        </div>

        <?php if ($suggestions !== []): ?>
            <section class="section">
                <div class="section-head"><h2>پیشنهاد ما برای تکمیل خرید</h2></div>
                <div class="product-grid">
                    <?php foreach ($suggestions as $product): ?>
                        <?php partial('product-card', ['product' => $product]); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>
