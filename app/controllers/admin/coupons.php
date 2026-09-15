<?php
/** کدهای تخفیف */

declare(strict_types=1);

function index(): void
{
    require_admin();

    render_admin('admin/coupons', [
        'coupons' => db_all('SELECT * FROM coupons ORDER BY id DESC'),
    ], ['title' => 'کدهای تخفیف']);
}

function save(): void
{
    require_admin();

    $code = mb_strtoupper(clean(input('code', ''), 40));
    $type = input('type', 'percent') === 'fixed' ? 'fixed' : 'percent';
    $amount = max(0, int_input('amount'));
    $minOrder = max(0, int_input('min_order'));
    $maxUses = max(0, int_input('max_uses'));
    $expiresAt = clean(input('expires_at', ''), 30);

    if ($code === '' || $amount <= 0) {
        flash_error('کد و مقدار تخفیف را وارد کنید.');
        redirect('/admin/coupons');
    }
    if ($type === 'percent' && $amount > 90) {
        flash_error('درصد تخفیف نمی‌تواند بیشتر از ۹۰ باشد.');
        redirect('/admin/coupons');
    }

    if (db_value('SELECT COUNT(*) FROM coupons WHERE UPPER(code) = :code', ['code' => $code]) > 0) {
        flash_error('این کد تخفیف قبلاً ثبت شده است.');
        redirect('/admin/coupons');
    }

    db_insert('coupons', [
        'code'       => $code,
        'type'       => $type,
        'amount'     => $amount,
        'min_order'  => $minOrder,
        'max_uses'   => $maxUses,
        'used_count' => 0,
        'expires_at' => $expiresAt !== '' ? date('Y-m-d H:i:s', strtotime(en_digits($expiresAt))) : null,
        'is_active'  => input('is_active') ? 1 : 0,
    ]);

    flash_success('کد تخفیف «' . $code . '» ایجاد شد.');
    redirect('/admin/coupons');
}

function delete(string $id): void
{
    require_admin();

    db_run('DELETE FROM coupons WHERE id = :id', ['id' => (int) $id]);
    flash_success('کد تخفیف حذف شد.');
    redirect('/admin/coupons');
}
