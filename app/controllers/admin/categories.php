<?php
/** مدیریت دسته‌بندی‌ها */

declare(strict_types=1);

function index(): void
{
    require_admin();

    $editId = (int) ($_GET['edit'] ?? 0);
    $categories = db_all(
        'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS products_count
         FROM categories c ORDER BY c.position, c.id'
    );

    render_admin('admin/categories', [
        'categories' => $categories,
        'editing'    => $editId > 0 ? db_one('SELECT * FROM categories WHERE id = :id', ['id' => $editId]) : null,
    ], ['title' => 'دسته‌بندی‌ها']);
}

function save(): void
{
    require_admin();

    $id = int_input('id');
    $name = clean(input('name', ''), 120);

    if (mb_strlen($name) < 2) {
        flash_error('نام دسته‌بندی را وارد کنید.');
        redirect('/admin/categories');
    }

    $slug = clean(input('slug', ''), 120);
    $slug = slugify($slug !== '' ? $slug : $name);

    $data = [
        'name'        => $name,
        'slug'        => $slug,
        'parent_id'   => int_input('parent_id') ?: null,
        'icon'        => clean(input('icon', ''), 20),
        'image'       => clean(input('image', ''), 255) ?: null,
        'description' => clean(input('description', ''), 500),
        'position'    => int_input('position'),
        'is_active'   => input('is_active') ? 1 : 0,
    ];

    $duplicate = db_value(
        'SELECT COUNT(*) FROM categories WHERE slug = :slug AND id <> :id',
        ['slug' => $slug, 'id' => $id]
    );
    if ($duplicate > 0) {
        $data['slug'] = $slug . '-' . random_int(10, 99);
    }

    if ($id > 0) {
        db_update('categories', $data, 'id = :id', ['id' => $id]);
        flash_success('دسته‌بندی بروزرسانی شد.');
    } else {
        db_insert('categories', $data);
        flash_success('دسته‌بندی «' . $name . '» اضافه شد.');
    }

    redirect('/admin/categories');
}

function delete(string $id): void
{
    require_admin();

    $category = db_one('SELECT * FROM categories WHERE id = :id', ['id' => (int) $id]);
    if ($category === null) {
        flash_error('دسته‌بندی یافت نشد.');
        redirect('/admin/categories');
    }

    $products = (int) db_value('SELECT COUNT(*) FROM products WHERE category_id = :id', ['id' => (int) $id], 0);
    if ($products > 0) {
        flash_error('این دسته‌بندی ' . fa_digits($products) . ' محصول دارد. ابتدا محصولات را منتقل یا غیرفعال کنید.');
        redirect('/admin/categories');
    }

    db_run('DELETE FROM categories WHERE id = :id', ['id' => (int) $id]);
    flash_success('دسته‌بندی حذف شد.');
    redirect('/admin/categories');
}
