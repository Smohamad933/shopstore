<?php
/**
 * کارت محصول (استفاده در همه لیست‌ها)
 * @var array $product
 */
$salePrice = product_price($product);
$hasDiscount = (int) $product['sale_price'] > 0 && (int) $product['sale_price'] < (int) $product['price'];
$available = (int) $product['stock'] > 0;
?>
<article class="product-card">
    <a class="product-media" href="<?= url(product_url($product)) ?>" aria-label="<?= e($product['name']) ?>">
        <div class="product-badges">
            <?php foreach (product_badges($product) as $badge): ?>
                <span class="badge <?= e($badge['class']) ?>"><?= e($badge['label']) ?></span>
            <?php endforeach; ?>
        </div>
        <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy" width="400" height="400">
    </a>

    <div class="product-body">
        <div class="product-brand"><?= e($product['brand']) ?></div>
        <h3 class="product-title"><a href="<?= url(product_url($product)) ?>"><?= e($product['name']) ?></a></h3>

        <div class="product-meta">
            <span class="rating">★ <span><?= fa_digits(number_format((float) $product['rating'], 1)) ?></span></span>
            <span>•</span>
            <span>(<?= fa_digits((int) $product['rating_count']) ?> دیدگاه)</span>
        </div>

        <div class="product-foot">
            <div class="price-box">
                <?php if ($hasDiscount): ?>
                    <span class="price-old"><?= money((int) $product['price']) ?></span>
                <?php endif; ?>
                <span class="price <?= $hasDiscount ? 'is-sale' : '' ?>">
                    <?= money($salePrice) ?><span class="unit">تومان</span>
                </span>
            </div>

            <?php if ($available): ?>
                <button class="add-to-cart" type="button"
                        data-add-to-cart="<?= (int) $product['id'] ?>"
                        aria-label="افزودن <?= e($product['name']) ?> به سبد خرید"
                        title="افزودن به سبد خرید">
                    <?= icon('cart') ?>
                </button>
            <?php else: ?>
                <span class="badge badge-muted">ناموجود</span>
            <?php endif; ?>
        </div>
    </div>
</article>
