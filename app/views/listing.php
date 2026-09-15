<?php
/**
 * ویو مشترک لیست محصولات (دسته‌بندی، جست‌وجو، تخفیف‌ها)
 * @var string $heading
 * @var array $products
 * @var array $pagination
 * @var array $filters
 * @var string $basePath
 * @var array $query
 * @var bool $showFilters
 */
$showFilters = $showFilters ?? false;
$brands = $brands ?? [];
$priceBounds = $priceBounds ?? ['min' => 0, 'max' => 0];
$sortOptions = [
    'newest'    => 'جدیدترین',
    'cheap'     => 'ارزان‌ترین',
    'expensive' => 'گران‌ترین',
    'popular'   => 'محبوب‌ترین',
    'discount'  => 'بیشترین تخفیف',
];
$currentSort = $filters['sort'] ?? 'newest';
?>
<div class="container">
    <?php partial('breadcrumb', ['items' => $breadcrumbs ?? []]); ?>
</div>

<div class="container">
    <div class="row-between wrap mb-2">
        <div>
            <h1 style="margin-bottom:2px;"><?= e($heading) ?></h1>
            <?php if (!empty($description)): ?>
                <p class="muted small mb-0"><?= e($description) ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($pagination['from'])): ?>
            <span class="muted small">
                نمایش <?= fa_digits($pagination['from']) ?> تا <?= fa_digits($pagination['to']) ?>
                از <?= fa_digits($pagination['total']) ?> کالا
            </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($children)): ?>
        <div class="row wrap mb-3">
            <span class="muted small">زیردسته‌ها:</span>
            <?php foreach ($children as $child): ?>
                <a class="chip" href="<?= url(category_url($child)) ?>"><?= e($child['name']) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="container">
    <div class="listing <?= $showFilters ? '' : 'no-filters' ?>" style="<?= $showFilters ? '' : 'grid-template-columns: minmax(0,1fr);' ?>">
        <?php if ($showFilters): ?>
            <aside class="filters" aria-label="فیلترها">
                <form method="get" action="<?= e(url($basePath)) ?>">
                    <div class="row-between" style="padding-block:12px;">
                        <b style="font-size:.92rem;">فیلترها</b>
                        <button class="btn btn-sm btn-ghost filter-close" type="button" data-filter-close>بستن</button>
                    </div>

                    <div class="filter-block">
                        <h3>محدوده قیمت (تومان)</h3>
                        <div class="price-range">
                            <input class="input" type="number" name="min_price" inputmode="numeric" placeholder="از <?= e(money($priceBounds['min'])) ?>"
                                   value="<?= !empty($filters['min_price']) ? (int) $filters['min_price'] : '' ?>">
                            <span class="muted">—</span>
                            <input class="input" type="number" name="max_price" inputmode="numeric" placeholder="تا <?= e(money($priceBounds['max'])) ?>"
                                   value="<?= !empty($filters['max_price']) ? (int) $filters['max_price'] : '' ?>">
                        </div>
                    </div>

                    <?php if ($brands !== []): ?>
                        <div class="filter-block">
                            <h3>برند</h3>
                            <div class="filter-list">
                                <?php foreach ($brands as $brand): ?>
                                    <?php $checked = in_array($brand['brand'], (array) ($filters['brand'] ?? []), true); ?>
                                    <label>
                                        <input type="checkbox" name="brand[]" value="<?= e($brand['brand']) ?>" <?= $checked ? 'checked' : '' ?>>
                                        <span><?= e($brand['brand']) ?></span>
                                        <span class="n"><?= fa_digits((int) $brand['total']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="filter-block">
                        <h3>وضعیت موجودی</h3>
                        <label class="checkbox">
                            <input type="checkbox" name="in_stock" value="1" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
                            <span>فقط کالاهای موجود</span>
                        </label>
                    </div>

                    <div class="filter-block">
                        <input type="hidden" name="sort" value="<?= e($currentSort) ?>">
                        <button class="btn btn-primary btn-block" type="submit">اعمال فیلتر</button>
                        <a class="btn btn-ghost btn-block mt-1" href="<?= e(url($basePath)) ?>">حذف فیلترها</a>
                    </div>
                </form>
            </aside>
        <?php endif; ?>

        <div>
            <div class="toolbar">
                <div class="sort-tabs">
                    <?php foreach ($sortOptions as $key => $label): ?>
                        <a class="<?= $currentSort === $key ? 'is-active' : '' ?>"
                           href="<?= e(page_url($basePath, array_merge($query, ['sort' => $key, 'page' => 1]), 1)) ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php if ($showFilters): ?>
                    <button class="btn btn-sm btn-ghost filter-toggle" type="button" data-filter-toggle>
                        <?= icon('filter') ?> فیلترها
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($products === []): ?>
                <?php partial('empty', [
                    'icon'        => 'search',
                    'title'       => 'محصولی با این مشخصات پیدا نشد',
                    'text'        => 'فیلترها را تغییر دهید یا عبارت دیگری را جست‌وجو کنید. کارشناسان ما هم می‌توانند کالای مورد نظر شما را تأمین کنند.',
                    'actionLabel' => 'تماس با پشتیبانی',
                    'actionUrl'   => url('/contact'),
                ]); ?>
            <?php else: ?>
                <div class="product-grid cols-3" style="<?= $showFilters ? '' : 'grid-template-columns: repeat(4, minmax(0,1fr));' ?>">
                    <?php foreach ($products as $product): ?>
                        <?php partial('product-card', ['product' => $product]); ?>
                    <?php endforeach; ?>
                </div>

                <?php partial('pagination', ['pagination' => $pagination, 'query' => $query]); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
