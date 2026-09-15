<?php
/**
 * صفحه محصول
 * @var array $product
 * @var array $gallery
 * @var array $specs
 * @var array $reviews
 * @var array $reviewStats
 * @var array $related
 */
$salePrice = product_price($product);
$hasDiscount = (int) $product['sale_price'] > 0 && (int) $product['sale_price'] < (int) $product['price'];
$available = (int) $product['stock'] > 0;
$bullets = array_filter(array_map('trim', explode("\n", (string) $product['short_desc'])));
$images = $gallery !== [] ? $gallery : [null];
?>
<div class="container">
    <?php partial('breadcrumb', ['items' => $breadcrumbs]); ?>
</div>

<div class="container">
    <div class="product-view">
        <div>
            <div class="gallery" data-gallery>
                <div class="gallery-thumbs">
                    <?php foreach ($images as $index => $image): ?>
                        <button type="button" data-gallery-thumb class="<?= $index === 0 ? 'is-active' : '' ?>">
                            <img src="<?= e(product_image($image)) ?>" alt="تصویر <?= fa_digits($index + 1) ?> <?= e($product['name']) ?>" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="gallery-main" data-gallery-main>
                    <img src="<?= e(product_image($images[0])) ?>" alt="<?= e($product['name']) ?>" fetchpriority="high">
                </div>
            </div>

            <div class="tabs" data-tabs>
                <div class="tab-nav">
                    <button type="button" data-tab="desc" class="is-active">توضیحات</button>
                    <button type="button" data-tab="specs">مشخصات فنی</button>
                    <button type="button" data-tab="reviews">دیدگاه‌ها (<?= fa_digits($reviewStats['total']) ?>)</button>
                    <button type="button" data-tab="shipping">ارسال و گارانتی</button>
                </div>

                <div class="tab-panel is-active" data-tab-panel="desc">
                    <div class="surface pad-lg">
                        <div class="prose">
                            <?= nl2br(e($product['description'])) ?>
                        </div>
                        <?php if ($bullets !== []): ?>
                            <h3 class="mt-2">نکات کلیدی</h3>
                            <ul class="summary-list">
                                <?php foreach ($bullets as $bullet): ?>
                                    <li><?= e($bullet) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-panel" data-tab-panel="specs">
                    <div class="surface" style="overflow:hidden;">
                        <?php if ($specs === []): ?>
                            <p class="muted pad">مشخصات فنی این کالا به‌زودی تکمیل می‌شود.</p>
                        <?php else: ?>
                            <table class="spec-table">
                                <tbody>
                                <?php foreach ($specs as $spec): ?>
                                    <tr>
                                        <th><?= e($spec['label'] ?? '') ?></th>
                                        <td><?= e($spec['value'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr><th>برند</th><td><?= e($product['brand']) ?></td></tr>
                                <tr><th>کد کالا</th><td><?= e($product['sku']) ?></td></tr>
                                <?php if ($product['category'] ?? null): ?>
                                    <tr><th>دسته‌بندی</th><td><?= e($product['category']['name']) ?></td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-panel" data-tab-panel="reviews">
                    <div class="grid" style="grid-template-columns: minmax(0,1fr) 340px; align-items:start;">
                        <div class="surface pad-lg">
                            <?php if ($reviews === []): ?>
                                <p class="muted small">هنوز دیدگاهی برای این کالا ثبت نشده است. اولین نفر باشید!</p>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review">
                                        <div class="review-head">
                                            <div class="row">
                                                <span class="avatar"><?= e(mb_substr($review['name'], 0, 1)) ?></span>
                                                <div>
                                                    <b class="small"><?= e($review['name']) ?></b>
                                                    <div class="tiny muted"><?= e(jdate($review['created_at'], 'long')) ?></div>
                                                </div>
                                            </div>
                                            <span class="rating"><?= stars((float) $review['rating']) ?></span>
                                        </div>
                                        <p class="small mb-0"><?= nl2br(e($review['comment'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="surface pad-lg">
                            <h3 style="font-size:1rem;">ثبت دیدگاه</h3>
                            <p class="tiny muted">تجربه خود از این کالا را بنویسید تا دیگران بهتر انتخاب کنند.</p>
                            <form method="post" action="<?= url('/product/' . $product['id'] . '-' . $product['slug'] . '/review') ?>">
                                <?= csrf_field() ?>
                                <div class="field">
                                    <label class="label" for="review-name">نام شما</label>
                                    <input class="input" id="review-name" type="text" name="name" required
                                           value="<?= e($user['name'] ?? '') ?>">
                                </div>
                                <div class="field">
                                    <label class="label">امتیاز شما</label>
                                    <div class="row" style="gap:6px;">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <label class="checkbox" style="gap:4px;">
                                                <input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                                                <span class="small"><?= fa_digits($i) ?> ★</span>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label" for="review-comment">متن دیدگاه</label>
                                    <textarea class="textarea" id="review-comment" name="comment" required minlength="10"></textarea>
                                </div>
                                <button class="btn btn-primary btn-block" type="submit">ارسال دیدگاه</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-panel" data-tab-panel="shipping">
                    <div class="surface pad-lg prose">
                        <p><b>ارسال:</b> سفارش‌های تهران توسط پیک (همان روز) و شهرستان‌ها با باربری/پست پیشتاز (۲ تا ۴ روز کاری) ارسال می‌شوند. برای سفارش‌های بالای <?= toman((int) setting('free_shipping_from')) ?> ارسال رایگان است.</p>
                        <p><b>گارانتی:</b> تمام کالاها با فاکتور رسمی و گارانتی شرکتی ارسال می‌شوند. مدت گارانتی در جدول مشخصات فنی درج شده است.</p>
                        <p><b>بازگشت کالا:</b> تا ۷ روز پس از تحویل، در صورت باز نشدن بسته‌بندی، امکان مرجوعی وجود دارد. برای کالاهای معیوب هزینه بازگشت با ما است.</p>
                        <p><b>پشتیبانی فنی:</b> در صورت نیاز به راهنمایی برای نصب یا انتخاب تجهیزات مکمل، با شماره <?= e(setting('phone')) ?> تماس بگیرید.</p>
                    </div>
                </div>
            </div>
        </div>

        <aside class="buy-box">
            <span class="small muted"><?= e($product['brand']) ?></span>
            <h1><?= e($product['name']) ?></h1>

            <div class="row wrap" style="gap:10px;">
                <span class="rating"><?= stars($reviewStats['avg'] ?: (float) $product['rating']) ?>
                    <span><?= fa_digits(number_format($reviewStats['avg'] ?: (float) $product['rating'], 1)) ?> از ۵</span>
                </span>
                <span class="tiny muted">(<?= fa_digits($reviewStats['total']) ?> دیدگاه)</span>
                <?php if ($product['sku']): ?>
                    <span class="tiny muted">کد کالا: <?= e($product['sku']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($bullets !== []): ?>
                <ul class="summary-list">
                    <?php foreach (array_slice($bullets, 0, 4) as $bullet): ?>
                        <li><?= e($bullet) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="price-panel">
                <?php if ($hasDiscount): ?>
                    <div class="row" style="gap:8px;">
                        <span class="badge badge-sale">٪<?= fa_digits(discount_percent((int) $product['price'], (int) $product['sale_price'])) ?> تخفیف</span>
                        <span class="price-old"><?= money((int) $product['price']) ?> تومان</span>
                    </div>
                <?php endif; ?>
                <div class="final"><?= money($salePrice) ?> <span class="small muted">تومان</span></div>
                <div class="tiny muted"><?= e(amount_in_words($salePrice)) ?></div>
            </div>

            <div class="row-between mb-2">
                <span class="small <?= $available ? 'muted' : 'strong' ?>" style="<?= $available ? '' : 'color:var(--sale)' ?>">
                    <?php if ($available): ?>
                        ✓ موجود در انبار <?= (int) $product['stock'] <= 3 ? '(تنها ' . fa_digits((int) $product['stock']) . ' عدد)' : '' ?>
                    <?php else: ?>
                        ✕ فعلاً ناموجود
                    <?php endif; ?>
                </span>
                <span class="tiny muted">ارسال از تهران</span>
            </div>

            <?php if ($available): ?>
                <div class="row" style="gap:10px;">
                    <div class="qty">
                        <button type="button" data-step="down" aria-label="کاهش">−</button>
                        <input type="number" value="1" min="1" max="<?= (int) $product['stock'] ?>" data-product-qty aria-label="تعداد">
                        <button type="button" data-step="up" aria-label="افزایش">+</button>
                    </div>
                    <button class="btn btn-primary grow" type="button" data-add-to-cart="<?= (int) $product['id'] ?>" data-use-qty>
                        <?= icon('cart') ?> افزودن به سبد خرید
                    </button>
                </div>
                <a class="btn btn-ghost btn-block mt-1" href="<?= url('/cart') ?>">مشاهده سبد خرید</a>
            <?php else: ?>
                <a class="btn btn-block" href="<?= url('/contact') ?>">اطلاع از موجود شدن</a>
            <?php endif; ?>

            <div class="mt-2" style="border-top:1px solid var(--line-2); padding-top:12px;">
                <div class="row small muted"><span><?= icon('truck') ?></span> ارسال رایگان بالای <?= toman((int) setting('free_shipping_from')) ?></div>
                <div class="row small muted mt-1"><span><?= icon('shield') ?></span> ضمانت اصالت و گارانتی شرکتی</div>
                <div class="row small muted mt-1"><span><?= icon('headset') ?></span> مشاوره فنی: <?= e(setting('phone')) ?></div>
            </div>
        </aside>
    </div>
</div>

<?php if ($related !== []): ?>
    <section class="section">
        <div class="container">
            <div class="section-head"><h2>محصولات مشابه</h2></div>
            <div class="product-grid">
                <?php foreach ($related as $product): ?>
                    <?php partial('product-card', ['product' => $product]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
