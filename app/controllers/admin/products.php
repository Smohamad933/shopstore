<?php
/** مدیریت محصولات: فهرست، افزودن/ویرایش، حذف */

declare(strict_types=1);

function index(): void
{
    require_admin();

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 15;
    $term = clean($_GET['q'] ?? '', 80);
    $categoryId = (int) ($_GET['category'] ?? 0);
    $stockFilter = (string) ($_GET['stock'] ?? '');

    $where = ['1 = 1'];
    $params = [];

    if ($term !== '') {
        $where[] = '(name LIKE :q OR sku LIKE :q OR brand LIKE :q)';
        $params['q'] = '%' . $term . '%';
    }
    if ($categoryId > 0) {
        $where[] = 'category_id = :category';
        $params['category'] = $categoryId;
    }
    if ($stockFilter === 'low') {
        $where[] = 'stock <= 3';
    } elseif ($stockFilter === 'out') {
        $where[] = 'stock = 0';
    }

    $whereSql = implode(' AND ', $where);
    $total = (int) db_value("SELECT COUNT(*) FROM products WHERE {$whereSql}", $params, 0);
    $offset = ($page - 1) * $perPage;

    $products = db_all(
        "SELECT p.*, c.name AS category_name FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE {$whereSql}
         ORDER BY p.id DESC LIMIT {$perPage} OFFSET {$offset}",
        $params
    );

    $pagination = paginate($total, $perPage, $page, '/admin/products', array_filter([
        'q' => $term, 'category' => $categoryId ?: null, 'stock' => $stockFilter ?: null,
    ]));
    $pagination['base'] = '/admin/products';

    render_admin('admin/products', [
        'products'   => $products,
        'categories' => nav_categories(),
        'pagination' => $pagination,
        'filters'    => ['q' => $term, 'category' => $categoryId, 'stock' => $stockFilter],
        'query'      => array_filter(['q' => $term, 'category' => $categoryId ?: null, 'stock' => $stockFilter ?: null]),
    ], ['title' => 'محصولات']);
}

/** فرم افزودن/ویرایش محصول */
function form(string $id = ''): void
{
    require_admin();

    $product = $id !== '' ? db_one('SELECT * FROM products WHERE id = :id', ['id' => (int) $id]) : null;
    if ($id !== '' && $product === null) {
        render_error(404);
        return;
    }

    $specs = $product ? product_specs($product) : [];
    $specLines = implode("\n", array_map(fn ($spec) => ($spec['label'] ?? '') . ' | ' . ($spec['value'] ?? ''), $specs));

    render_admin('admin/product-form', [
        'product'    => $product,
        'categories' => nav_categories(),
        'specLines'  => $specLines,
        'galleryLines' => $product ? implode("\n", json_decode((string) $product['gallery'], true) ?: []) : '',
    ], ['title' => $product ? 'ویرایش محصول' : 'افزودن محصول']);
}

/** ذخیره محصول (افزودن یا ویرایش) */
function save(): void
{
    require_admin();

    $id = int_input('id');
    $name = clean(input('name', ''), 200);
    $slugInput = clean(input('slug', ''), 200);

    if (mb_strlen($name) < 3) {
        flash_error('نام محصول را کامل وارد کنید.');
        redirect('/admin/products');
    }

    $slug = $slugInput !== '' ? slugify($slugInput) : slugify($name);
    $slug = admin_unique_slug($slug, $id);

    $specs = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) input('specs', '')) as $line) {
        $line = trim($line);
        if ($line === '' || !str_contains($line, '|')) {
            continue;
        }
        [$label, $value] = array_map('trim', explode('|', $line, 2));
        if ($label !== '') {
            $specs[] = ['label' => $label, 'value' => $value];
        }
    }

    $gallery = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) input('gallery', '')) as $line) {
        $line = trim($line);
        if ($line !== '') {
            $gallery[] = $line;
        }
    }

    $image = clean(input('image', ''), 255);
    $uploaded = admin_upload_image('image_file');
    if ($uploaded !== null) {
        $image = $uploaded;
    }

    $galleryInput = clean(input('gallery_upload_path', ''), 255);
    if ($galleryInput !== '') {
        $gallery[] = $galleryInput;
    }

    $data = [
        'name'           => $name,
        'slug'           => $slug,
        'sku'            => clean(input('sku', ''), 60) ?: null,
        'category_id'    => int_input('category_id'),
        'brand'          => clean(input('brand', ''), 80),
        'price'          => max(0, int_input('price')),
        'sale_price'     => max(0, int_input('sale_price')),
        'stock'          => max(0, int_input('stock')),
        'image'          => $image ?: null,
        'gallery'        => json_encode($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'short_desc'     => clean(input('short_desc', ''), 600),
        'description'    => clean(input('description', ''), 4000),
        'specs'          => json_encode($specs, JSON_UNESCAPED_UNICODE),
        'is_featured'    => input('is_featured') ? 1 : 0,
        'is_special'     => input('is_special') ? 1 : 0,
        'special_ends_at' => clean(input('special_ends_at', ''), 30) ?: null,
        'is_active'      => input('is_active') ? 1 : 0,
        'updated_at'     => date('Y-m-d H:i:s'),
    ];

    if ($data['category_id'] <= 0) {
        flash_error('دسته‌بندی محصول را انتخاب کنید.');
        redirect('/admin/products');
    }

    if ($id > 0) {
        db_update('products', $data, 'id = :id', ['id' => $id]);
        flash_success('محصول «' . $name . '» بروزرسانی شد.');
    } else {
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = db_insert('products', $data);
        flash_success('محصول «' . $name . '» اضافه شد.');
    }

    redirect('/admin/products/' . $id . '/edit');
}

function delete(string $id): void
{
    require_admin();

    $product = db_one('SELECT name FROM products WHERE id = :id', ['id' => (int) $id]);
    if ($product === null) {
        flash_error('محصول یافت نشد.');
        redirect('/admin/products');
    }

    db_run('UPDATE products SET is_active = 0, updated_at = :now WHERE id = :id', [
        'now' => date('Y-m-d H:i:s'),
        'id'  => (int) $id,
    ]);

    flash_success('محصول «' . $product['name'] . '» غیرفعال شد (سفارش‌های قبلی حفظ شد).');
    redirect('/admin/products');
}

/** ساخت slug یکتا */
function admin_unique_slug(string $slug, int $ignoreId = 0): string
{
    $candidate = $slug;
    $counter = 1;
    while (db_value('SELECT COUNT(*) FROM products WHERE slug = :slug AND id <> :id', ['slug' => $candidate, 'id' => $ignoreId]) > 0) {
        $candidate = $slug . '-' . (++$counter);
    }
    return $candidate;
}

/** آپلود امن تصویر محصول و برگرداندن مسیر نسبی */
function admin_upload_image(string $field): ?string
{
    if (empty($_FILES[$field]['name']) || (int) ($_FILES[$field]['error'] ?? 4) !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$field];
    if ((int) $file['size'] > 3 * 1024 * 1024) {
        flash_error('حجم تصویر باید کمتر از ۳ مگابایت باشد.');
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, (string) $file['tmp_name']) ?: null;
        finfo_close($finfo);
    }
    if ($mime === null) {
        $mime = (string) ($file['type'] ?? '');
    }
    if (!isset($allowed[$mime])) {
        flash_error('فرمت تصویر مجاز نیست (فقط JPG، PNG یا WEBP).');
        return null;
    }

    $dir = (string) config('products_media');
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        flash_error('پوشه آپلود قابل نوشتن نیست: public/uploads/products');
        return null;
    }

    $fileName = date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    if (!move_uploaded_file((string) $file['tmp_name'], $dir . '/' . $fileName)) {
        flash_error('ذخیره تصویر ناموفق بود.');
        return null;
    }

    return 'uploads/products/' . $fileName;
}
