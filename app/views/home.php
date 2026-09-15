<?php
/**
 * صفحه اصلی
 * @var array $specials
 * @var array $featured
 * @var array $latest
 * @var array $bestSellers
 * @var array $brands
 * @var array $categories
 */
$ventilation = category_by_slug($categories, 'ventilation');
$pipes = category_by_slug($categories, 'pipes-and-water');
$firstSpecial = $specials[0] ?? null;
?>

<div class="container section-tight">
    <div class="hero" data-hero>
        <div class="hero-slide is-active">
            <img src="<?= url('/assets/img/hero-1.jpg') ?>" alt="تجهیزات سرمایش و گرمایش" fetchpriority="high">
            <div class="hero-content">
                <span class="eyebrow">فصل تخفیف تجهیزات تأسیسات</span>
                <h1>سرمایش و گرمایش، با قیمت پروژه‌ای</h1>
                <p>داکت اسپلیت، پکیج و رادیاتور با گارانتی رسمی و ارسال سریع. مشاوره فنی رایگان پیش از خرید.</p>
                <div class="hero-actions">
                    <a class="btn btn-lg" href="<?= url('/discounts') ?>">مشاهده تخفیف‌ها</a>
                    <?php if ($ventilation): ?>
                        <a class="btn btn-lg btn-ghost" href="<?= url(category_url($ventilation)) ?>">دسته تهویه مطبوع</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <img src="<?= url('/assets/img/hero-2.jpg') ?>" alt="لوله، اتصالات و شیرآلات" loading="lazy">
            <div class="hero-content">
                <span class="eyebrow">تأمین پروژه‌ای</span>
                <h1>لوله، اتصالات و شیرآلات</h1>
                <p>خرید عمده برای پروژه‌های ساختمانی با فاکتور رسمی، قیمت ویژه و ارسال به سراسر کشور.</p>
                <div class="hero-actions">
                    <?php if ($pipes): ?>
                        <a class="btn btn-lg" href="<?= url(category_url($pipes)) ?>">ورود به دسته‌بندی</a>
                    <?php endif; ?>
                    <a class="btn btn-lg btn-ghost" href="tel:<?= e(en_digits(setting('phone'))) ?>">تماس با کارشناس فروش</a>
                </div>
            </div>
        </div>
        <div class="hero-dots">
            <button type="button" data-hero-dot class="is-active" aria-label="اسلاید ۱"></button>
            <button type="button" data-hero-dot aria-label="اسلاید ۲"></button>
        </div>
    </div>
</div>

<div class="container section-tight">
    <div class="feature-strip">
        <div class="feature"><?= icon('truck') ?><div><b>ارسال سریع</b><span>تهران همان روز، شهرستان‌ها ۲ تا ۴ روز</span></div></div>
        <div class="feature"><?= icon('shield') ?><div><b>ضمانت اصالت کالا</b><span>فاکتور رسمی و گارانتی شرکتی</span></div></div>
        <div class="feature"><?= icon('bolt') ?><div><b>پرداخت در محل</b><span>برای سفارش‌های شهر تهران</span></div></div>
        <div class="feature"><?= icon('headset') ?><div><b>مشاوره فنی</b><span>انتخاب درست تجهیزات، رایگان</span></div></div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>خرید بر اساس دسته‌بندی</h2>
            <a class="link" href="<?= url('/discounts') ?>">همه محصولات</a>
        </div>
        <div class="category-tiles">
            <?php foreach ($categories as $category): ?>
                <a class="category-tile" href="<?= url(category_url($category)) ?>">
                    <span class="thumb">
                        <?php if ($category['image']): ?>
                            <img src="<?= e(product_image($category['image'])) ?>" alt="<?= e($category['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <?= e($category['icon']) ?>
                        <?php endif; ?>
                    </span>
                    <b><?= e($category['name']) ?></b>
                    <span><?= fa_digits((int) $category['product_count']) ?> کالا</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($specials !== []): ?>
    <section class="special-strip">
        <div class="container">
            <div class="section-head">
                <h2>پیشنهاد شگفت‌انگیز</h2>
                <?php if ($firstSpecial && $firstSpecial['special_ends_at']): ?>
                    <div class="countdown" data-countdown="<?= e($firstSpecial['special_ends_at']) ?>">
                        <span>پایان تخفیف تا</span>
                        <b data-cd="hours">۰۰</b><span>ساعت</span>
                        <b data-cd="minutes">۰۰</b><span>دقیقه</span>
                        <b data-cd="seconds">۰۰</b><span>ثانیه</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="product-grid">
                <?php foreach ($specials as $product): ?>
                    <?php partial('product-card', ['product' => $product]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>پرفروش‌ترین تجهیزات</h2>
            <a class="link" href="<?= url('/discounts') ?>">مشاهده همه</a>
        </div>
        <div class="product-grid">
            <?php foreach ($bestSellers as $product): ?>
                <?php partial('product-card', ['product' => $product]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-tight">
    <div class="container">
        <div class="surface pad-lg" style="display:grid; grid-template-columns: 1.4fr 1fr; gap: 24px; align-items: center;">
            <div>
                <span class="badge badge-new">مشاوره تخصصی پروژه</span>
                <h2 class="mt-1">تجهیزات پروژه‌تان را با کارشناس ما انتخاب کنید</h2>
                <p class="muted small mb-2">
                    برای پروژه‌های مسکونی و اداری، لیست تجهیزات و نقشه را برای ما بفرستید؛ پیشنهاد قیمت و
                    لیست جایگزین‌های اقتصادی را دریافت می‌کنید. صدور فاکتور رسمی و ارسال مرحله‌ای امکان‌پذیر است.
                </p>
                <div class="row wrap">
                    <a class="btn btn-primary" href="<?= url('/contact') ?>">درخواست استعلام قیمت</a>
                    <a class="btn btn-ghost" href="tel:<?= e(en_digits(setting('phone'))) ?>"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
                </div>
            </div>
            <div class="feature-strip" style="grid-template-columns: 1fr 1fr;">
                <div class="feature"><?= icon('package') ?><div><b>ارسال مرحله‌ای</b><span>طبق برنامه پروژه</span></div></div>
                <div class="feature"><?= icon('tag') ?><div><b>قیمت عمده</b><span>برای متراژ بالا</span></div></div>
                <div class="feature"><?= icon('clock') ?><div><b>پشتیبانی ۷ روز</b><span>پاسخ سریع به سؤالات فنی</span></div></div>
                <div class="feature"><?= icon('check') ?><div><b>تأمین کامل</b><span>یک خرید، یک فاکتور</span></div></div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>جدیدترین محصولات</h2>
            <a class="link" href="<?= url('/search') ?>">جست‌وجوی محصولات</a>
        </div>
        <div class="product-grid">
            <?php foreach ($latest as $product): ?>
                <?php partial('product-card', ['product' => $product]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($brands !== []): ?>
    <section class="section-tight">
        <div class="container">
            <div class="section-head"><h2>برندهای موجود</h2></div>
            <div class="brand-strip">
                <?php foreach ($brands as $brand): ?>
                    <a class="brand-pill" href="<?= e(url('/search', ['q' => $brand['brand']])) ?>">
                        <?= e($brand['brand']) ?> <span class="muted tiny">(<?= fa_digits((int) $brand['total']) ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
