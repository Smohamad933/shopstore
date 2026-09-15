<?php
/**
 * فرم افزودن/ویرایش محصول
 * @var array|null $product
 * @var array $categories
 * @var string $specLines
 * @var string $galleryLines
 */
$isEdit = $product !== null;
$value = fn (string $key, $default = '') => e($product[$key] ?? $default);
?>
<div class="row-between wrap mb-2">
    <a class="btn btn-sm btn-ghost" href="<?= url('/admin/products') ?>"><?= icon('chevron-r') ?> بازگشت به فهرست محصولات</a>
    <?php if ($isEdit): ?>
        <div class="row">
            <span class="pill <?= (int) $product['is_active'] === 1 ? 'pill-ok' : 'pill-danger' ?>">
                <?= (int) $product['is_active'] === 1 ? 'فعال در سایت' : 'غیرفعال' ?>
            </span>
            <a class="btn btn-sm" href="<?= url(product_url($product)) ?>" target="_blank"><?= icon('eye') ?> مشاهده در سایت</a>
        </div>
    <?php endif; ?>
</div>

<form class="admin-form" method="post" action="<?= url('/admin/products/save') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($product['id'] ?? 0) ?>">

    <div class="admin-grid">
        <div>
            <div class="card mb-2">
                <div class="card-head"><h3>اطلاعات اصلی</h3></div>
                <div class="card-body">
                    <div class="field">
                        <label class="label" for="name">نام محصول <span class="req">*</span></label>
                        <input class="input" id="name" type="text" name="name" required value="<?= $value('name') ?>">
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label class="label" for="slug">نامک (URL)</label>
                            <input class="input" id="slug" type="text" name="slug" value="<?= $value('slug') ?>" placeholder="duct-split-24000">
                            <div class="hint">خالی بگذارید تا از نام محصول ساخته شود.</div>
                        </div>
                        <div class="field">
                            <label class="label" for="sku">کد کالا (SKU)</label>
                            <input class="input" id="sku" type="text" name="sku" value="<?= $value('sku') ?>">
                        </div>
                        <div class="field">
                            <label class="label" for="category_id">دسته‌بندی <span class="req">*</span></label>
                            <select class="select" id="category_id" name="category_id" required>
                                <option value="">انتخاب کنید…</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="brand">برند</label>
                            <input class="input" id="brand" type="text" name="brand" value="<?= $value('brand') ?>" list="brand-list">
                            <datalist id="brand-list">
                                <?php foreach (catalog_brands() as $brand): ?>
                                    <option value="<?= e($brand['brand']) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-2">
                <div class="card-head"><h3>قیمت و موجودی</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="field">
                            <label class="label" for="price">قیمت (تومان) <span class="req">*</span></label>
                            <input class="input" id="price" type="number" name="price" required min="0" step="1000" value="<?= (int) ($product['price'] ?? 0) ?>">
                        </div>
                        <div class="field">
                            <label class="label" for="sale_price">قیمت با تخفیف (تومان)</label>
                            <input class="input" id="sale_price" type="number" name="sale_price" min="0" step="1000" value="<?= (int) ($product['sale_price'] ?? 0) ?>">
                            <div class="hint">صفر بگذارید تا تخفیفی نمایش داده نشود.</div>
                        </div>
                        <div class="field">
                            <label class="label" for="stock">موجودی انبار</label>
                            <input class="input" id="stock" type="number" name="stock" min="0" value="<?= (int) ($product['stock'] ?? 0) ?>">
                        </div>
                        <div class="field">
                            <label class="label" for="special_ends_at">پایان تخفیف (YYYY-MM-DD HH:MM:SS)</label>
                            <input class="input" id="special_ends_at" type="text" name="special_ends_at"
                                   value="<?= $value('special_ends_at') ?>" placeholder="2026-10-01 23:59:59">
                            <div class="hint">برای نمایش شمارنده معکوس در صفحه اصلی.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-2">
                <div class="card-head"><h3>توضیحات و مشخصات</h3></div>
                <div class="card-body">
                    <div class="field">
                        <label class="label" for="short_desc">ویژگی‌های کلیدی (هر خط یک ویژگی)</label>
                        <textarea class="textarea" id="short_desc" name="short_desc" rows="5"><?= $value('short_desc') ?></textarea>
                    </div>
                    <div class="field">
                        <label class="label" for="description">توضیح کامل محصول</label>
                        <textarea class="textarea" id="description" name="description" rows="6"><?= $value('description') ?></textarea>
                    </div>
                    <div class="field">
                        <label class="label" for="specs">مشخصات فنی (هر خط: عنوان | مقدار)</label>
                        <textarea class="textarea" id="specs" name="specs" rows="6" placeholder="ظرفیت | ۲۴۰۰۰ BTU"><?= e($specLines) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card mb-2">
                <div class="card-head"><h3>تصاویر</h3></div>
                <div class="card-body">
                    <div class="field">
                        <label class="label" for="image">تصویر اصلی (مسیر یا آدرس کامل)</label>
                        <input class="input" id="image" type="text" name="image" value="<?= $value('image') ?>" placeholder="assets/img/p-duct-ac.jpg">
                    </div>
                    <div class="field">
                        <label class="label" for="image_file">آپلود تصویر جدید</label>
                        <input class="input" id="image_file" type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
                        <div class="hint">JPG/PNG/WEBP — حداکثر ۳ مگابایت. تصویر آپلودی جای تصویر اصلی را می‌گیرد.</div>
                    </div>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= e(product_image($product['image'])) ?>" alt="" style="width:100%; max-width:220px; border-radius:12px; border:1px solid var(--line);">
                    <?php endif; ?>
                    <div class="field mt-2">
                        <label class="label" for="gallery">گالری (هر خط یک مسیر تصویر)</label>
                        <textarea class="textarea" id="gallery" name="gallery" rows="4" style="min-height:90px;"><?= e($galleryLines) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-2">
                <div class="card-head"><h3>نمایش در سایت</h3></div>
                <div class="card-body">
                    <label class="checkbox mb-1"><input type="checkbox" name="is_active" value="1" <?= (int) ($product['is_active'] ?? 1) === 1 ? 'checked' : '' ?>><span>محصول فعال باشد</span></label>
                    <label class="checkbox mb-1"><input type="checkbox" name="is_featured" value="1" <?= (int) ($product['is_featured'] ?? 0) === 1 ? 'checked' : '' ?>><span>نمایش در بخش محصولات ویژه صفحه اصلی</span></label>
                    <label class="checkbox mb-2"><input type="checkbox" name="is_special" value="1" <?= (int) ($product['is_special'] ?? 0) === 1 ? 'checked' : '' ?>><span>نمایش در «پیشنهاد شگفت‌انگیز» و صفحه تخفیف‌ها</span></label>

                    <button class="btn btn-primary btn-block" type="submit">ذخیره محصول</button>
                    <?php if ($isEdit): ?>
                        <a class="btn btn-ghost btn-block mt-1" href="<?= url('/admin/products') ?>">انصراف</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isEdit): ?>
                <div class="card">
                    <div class="card-head"><h3>یادداشت</h3></div>
                    <div class="card-body small muted">
                        ثبت‌شده: <?= e(jdate($product['created_at'], 'date_time')) ?><br>
                        آخرین ویرایش: <?= $product['updated_at'] ? e(jdate($product['updated_at'], 'date_time')) : '—' ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>
