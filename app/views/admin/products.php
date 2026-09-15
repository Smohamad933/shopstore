<?php
/**
 * فهرست محصولات در پنل مدیریت
 * @var array $products
 * @var array $categories
 * @var array $pagination
 * @var array $filters
 * @var array $query
 */
?>
<form class="toolbar-admin" method="get" action="<?= url('/admin/products') ?>">
    <input class="input" type="search" name="q" placeholder="جست‌وجو: نام، برند یا کد کالا" value="<?= e($filters['q']) ?>">
    <select class="select" name="category">
        <option value="">همه دسته‌بندی‌ها</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int) $category['id'] ?>" <?= (int) $filters['category'] === (int) $category['id'] ? 'selected' : '' ?>>
                <?= e($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="stock">
        <option value="">وضعیت موجودی</option>
        <option value="low" <?= $filters['stock'] === 'low' ? 'selected' : '' ?>>موجودی کم (≤ ۳)</option>
        <option value="out" <?= $filters['stock'] === 'out' ? 'selected' : '' ?>>ناموجود</option>
    </select>
    <button class="btn btn-sm" type="submit">فیلتر</button>
    <a class="btn btn-sm btn-ghost" href="<?= url('/admin/products') ?>">حذف فیلتر</a>
    <span class="muted small" style="margin-inline-start:auto;"><?= fa_digits($pagination['total']) ?> محصول</span>
    <a class="btn btn-sm btn-primary" href="<?= url('/admin/products/new') ?>">+ افزودن محصول</a>
</form>

<div class="card">
    <?php if ($products === []): ?>
        <div class="empty-admin">محصولی با این مشخصات پیدا نشد.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>تصویر</th>
                    <th>نام محصول</th>
                    <th>دسته‌بندی</th>
                    <th>قیمت</th>
                    <th>موجودی</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><img class="thumb-xs" src="<?= e(product_image($product['image'])) ?>" alt=""></td>
                        <td style="min-width:220px;">
                            <a class="small strong" href="<?= url('/admin/products/' . (int) $product['id'] . '/edit') ?>"><?= e(mb_substr($product['name'], 0, 48)) ?></a>
                            <div class="tiny muted">
                                <?= e($product['brand']) ?>
                                <?= $product['sku'] ? ' • ' . e($product['sku']) : '' ?>
                            </div>
                        </td>
                        <td class="small"><?= e($product['category_name']) ?></td>
                        <td class="nowrap small">
                            <?php if ((int) $product['sale_price'] > 0): ?>
                                <span class="pill pill-danger"><?= money((int) $product['sale_price']) ?></span><br>
                                <span class="tiny muted" style="text-decoration:line-through"><?= money((int) $product['price']) ?></span>
                            <?php else: ?>
                                <?= money((int) $product['price']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="pill <?= (int) $product['stock'] === 0 ? 'pill-danger' : ((int) $product['stock'] <= 3 ? 'pill-warn' : 'pill-ok') ?>">
                                <?= fa_digits((int) $product['stock']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="pill <?= (int) $product['is_active'] === 1 ? 'pill-ok' : 'pill-danger' ?>">
                                <?= (int) $product['is_active'] === 1 ? 'فعال' : 'غیرفعال' ?>
                            </span>
                            <?php if ((int) $product['is_featured'] === 1): ?><span class="pill pill-info">ویژه</span><?php endif; ?>
                            <?php if ((int) $product['is_special'] === 1): ?><span class="pill pill-warn">تخفیف</span><?php endif; ?>
                        </td>
                        <td class="nowrap">
                            <div class="actions-row">
                                <a class="btn btn-sm btn-ghost" href="<?= url('/admin/products/' . (int) $product['id'] . '/edit') ?>"><?= icon('edit') ?></a>
                                <a class="btn btn-sm btn-ghost" href="<?= url(product_url($product)) ?>" target="_blank" title="مشاهده در سایت"><?= icon('eye') ?></a>
                                <form method="post" action="<?= url('/admin/products/' . (int) $product['id'] . '/delete') ?>"
                                      data-confirm="محصول «<?= e($product['name']) ?>» غیرفعال شود؟">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger" type="submit"><?= icon('trash') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php partial('pagination', ['pagination' => $pagination, 'query' => $query]); ?>
