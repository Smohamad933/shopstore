<?php
/** تنظیمات فروشگاه */

declare(strict_types=1);

function index(): void
{
    require_admin();

    render_admin('admin/settings', [
        'grouped' => [
            'اطلاعات فروشگاه' => ['site_name', 'site_tagline', 'site_description', 'announcement'],
            'اطلاعات تماس' => ['phone', 'mobile', 'email', 'address', 'work_hours'],
            'شبکه‌های اجتماعی' => ['instagram', 'telegram', 'whatsapp'],
            'ارسال و پرداخت' => ['shipping_flat', 'free_shipping_from', 'online_enabled', 'cod_enabled', 'gateway', 'zarinpal_merchant_id'],
            'متن‌ها' => ['about_text'],
        ],
        'labels' => [
            'site_name' => 'نام فروشگاه',
            'site_tagline' => 'شعار / زیرعنوان',
            'site_description' => 'توضیح کوتاه سایت (SEO)',
            'announcement' => 'متن نوار بالای سایت',
            'phone' => 'تلفن ثابت',
            'mobile' => 'موبایل / واتس‌اپ',
            'email' => 'ایمیل',
            'address' => 'نشانی',
            'work_hours' => 'ساعات کاری',
            'instagram' => 'اینستاگرام (URL)',
            'telegram' => 'تلگرام (URL)',
            'whatsapp' => 'واتس‌اپ (URL)',
            'shipping_flat' => 'هزینه پایه ارسال (تومان)',
            'free_shipping_from' => 'ارسال رایگان از مبلغ (تومان)',
            'online_enabled' => 'پرداخت آنلاین فعال',
            'cod_enabled' => 'پرداخت در محل فعال',
            'gateway' => 'درگاه پرداخت',
            'zarinpal_merchant_id' => 'Merchant ID زرین‌پال',
            'about_text' => 'متن درباره ما',
        ],
        'longFields' => ['site_description', 'announcement', 'about_text', 'address'],
    ], ['title' => 'تنظیمات فروشگاه']);
}

function save(): void
{
    require_admin();

    $keys = [
        'site_name', 'site_tagline', 'site_description', 'announcement',
        'phone', 'mobile', 'email', 'address', 'work_hours',
        'instagram', 'telegram', 'whatsapp',
        'shipping_flat', 'free_shipping_from', 'gateway', 'zarinpal_merchant_id',
        'about_text',
    ];

    foreach ($keys as $key) {
        $value = input($key, null);
        if ($value !== null) {
            // مبالغ و شماره‌ها با ارقام لاتین ذخیره می‌شوند
            if (in_array($key, ['shipping_flat', 'free_shipping_from'], true)) {
                $value = (string) max(0, (int) en_digits((string) $value));
            }
            setting_set($key, clean($value, 2000));
        }
    }

    // چک‌باکس‌ها اگر ارسال نشوند یعنی خاموش
    foreach (['online_enabled', 'cod_enabled'] as $toggle) {
        setting_set($toggle, input($toggle) ? '1' : '0');
    }

    setting_set('asset_version', (string) (time())); // شکستن کش CSS/JS سبک

    flash_success('تنظیمات با موفقیت ذخیره شد.');
    redirect('/admin/settings');
}
