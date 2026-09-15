<?php
/**
 * مدیریت دسته‌بندی‌ها
 * @var array $categories
 * @var array|null $editing
 */
?>
<div class="admin-grid">
    <div class="card">
        <div class="card-head">
            <h3>دسته‌بندی‌ها (<?= fa_digits(count($categories)) ?>)</h3>
            <span class="muted small">ترتیب نمایش با فیلد «ترتیب» مشخص می‌شود</span>
        </div>
        <?php if ($categories === []): ?>
            <div class="empty-admin">دسته‌بندی‌ای ثبت نشده است.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr><th>#</th><th>نام</th><th>نامک</th><th>محصولات</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="tiny muted"><?= fa_digits((int) $category['id']) ?></td>
                            <td>
                                <b class="small"><?= e($category['icon']) ?> <?= e($category['name']) ?></b>
                                <?php if ((int) $category['parent_id'] > 0): ?>
                                    <span class="pill">زیرمجموعه</span>
                                <?php endif; ?>
                                <div class="tiny muted"><?= e(mb_substr((string) $category['description'], 0, 60)) ?></div>
                            </td>
                            <td class="tiny" style="direction:ltr; text-align:right;"><?= e($category['slug']) ?></td>
                            <td><?= fa_digits((int) $category['products_count']) ?></td>
                            <td><?= fa_digits((int) $category['position']) ?></td>
                            <td><span class="pill <?= (int) $category['is_active'] === 1 ? 'pill-ok' : 'pill-danger' ?>"><?= (int) $category['is_active'] === 1 ? 'فعال' : 'غیرفعال' ?></span></td>
                            <td class="nowrap">
                                <div class="actions-row">
                                    <a class="btn btn-sm btn-ghost" href="<?= url('/admin/categories?edit=' . (int) $category['id']) ?>"><?= icon('edit') ?></a>
                                    <a class="btn btn-sm btn-ghost" href="<?= url(category_url($category)) ?>" target="_blank"><?= icon('eye') ?></a>
                                    <form method="post" action="<?= url('/admin/categories/' . (int) $category['id'] . '/delete') ?>"
                                          data-confirm="دسته‌بندی «<?= e($category['name']) ?>» حذف شود؟">
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

    <div class="card">
        <div class="card-head"><h3><?= $editing ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی' ?></h3></div>
        <div class="card-body">
            <form class="admin-form" method="post" action="<?= url('/admin/categories/save') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">

                <div class="field">
                    <label class="label" for="name">نام <span class="req">*</span></label>
                    <input class="input" id="name" type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>">
                </div>
                <div class="field">
                    <label class="label" for="slug">نامک (لاتین)</label>
                    <input class="input" id="slug" type="text" name="slug" value="<?= e($editing['slug'] ?? '') ?>" placeholder="ventilation">
                </div>
                <div class="field">
                    <label class="label" for="parent_id">دسته والد</label>
                    <select class="select" id="parent_id" name="parent_id">
                        <option value="">— بدون والد —</option>
                        <?php foreach ($categories as $category): ?>
                            <?php if ((int) ($editing['id'] ?? 0) === (int) $category['id']) continue; ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) ($editing['parent_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="icon">آیکون (ایموجی)</label>
                        <input class="input" id="icon" type="text" name="icon" value="<?= e($editing['icon'] ?? '') ?>" placeholder="❄️">
                    </div>
                    <div class="field">
                        <label class="label" for="position">ترتیب</label>
                        <input class="input" id="position" type="number" name="position" value="<?= (int) ($editing['position'] ?? 0) ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label" for="image">تصویر دسته‌بندی</label>
                    <input class="input" id="image" type="text" name="image" value="<?= e($editing['image'] ?? '') ?>" placeholder="assets/img/p-duct-ac.jpg">
                </div>
                <div class="field">
                    <label class="label" for="description">توضیح کوتاه</label>
                    <textarea class="textarea" id="description" name="description" style="min-height:80px;"><?= e($editing['description'] ?? '') ?></textarea>
                </div>
                <label class="checkbox mb-2">
                    <input type="checkbox" name="is_active" value="1" <?= (int) ($editing['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
                    <span>نمایش در سایت</span>
                </label>

                <button class="btn btn-primary btn-block" type="submit"><?= $editing ? 'ذخیره تغییرات' : 'افزودن دسته‌بندی' ?></button>
                <?php if ($editing): ?>
                    <a class="btn btn-ghost btn-block mt-1" href="<?= url('/admin/categories') ?>">انصراف</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
