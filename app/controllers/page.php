<?php
/**
 * صفحات ثابت: درباره ما، تماس، سؤالات متداول، قوانین و صفحه آفلاین
 */

declare(strict_types=1);

function about(): void
{
    render('pages/about', [
        'brands' => array_slice(catalog_brands(), 0, 12),
        'stats'  => [
            'products'  => (int) db_value('SELECT COUNT(*) FROM products WHERE is_active = 1', [], 0),
            'orders'    => (int) db_value('SELECT COUNT(*) FROM orders', [], 0),
            'customers' => (int) db_value("SELECT COUNT(*) FROM users WHERE role = 'customer'", [], 0),
            'brands'    => count(catalog_brands()),
        ],
    ], [
        'title'      => 'درباره ' . setting('site_name'),
        'body_class' => 'page-about',
    ]);
}

function contact(): void
{
    render('pages/contact', [], [
        'title'      => 'تماس با ما',
        'body_class' => 'page-contact',
    ]);
}

function contact_send(): void
{
    $name = clean(input('name', ''), 120);
    $phone = en_digits((string) input('phone', ''));
    $email = clean(input('email', ''), 120);
    $subject = clean(input('subject', ''), 160);
    $message = clean(input('message', ''), 2000);

    if (mb_strlen($name) < 3 || mb_strlen($message) < 10) {
        flash_error('نام و متن پیام را کامل وارد کنید.');
        redirect('/contact');
    }

    db_insert('contact_messages', [
        'name'       => $name,
        'phone'      => $phone ?: null,
        'email'      => $email ?: null,
        'subject'    => $subject ?: 'بدون موضوع',
        'message'    => $message,
        'is_read'    => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    flash_success('پیام شما ثبت شد. کارشناسان ما در اولین فرصت پاسخ می‌دهند.');
    redirect('/contact');
}

function faq(): void
{
    $items = [
        ['چطور سفارش ثبت کنم؟', 'محصول را به سبد خرید اضافه کنید، وارد صفحه سبد خرید شوید و روی «تکمیل سفارش» بزنید. اطلاعات ارسال را وارد کنید و روش پرداخت را انتخاب کنید.'],
        ['ارسال چقدر طول می‌کشد؟', 'سفارش‌های تهران با پیک همان روز و شهرستان‌ها با باربری بین ۲ تا ۴ روز کاری ارسال می‌شوند. برای سفارش‌های بالای مبلغ مشخص‌شده ارسال رایگان است.'],
        ['امکان پرداخت در محل وجود دارد؟', 'بله، برای شهر تهران امکان پرداخت در محل فعال است. برای شهرستان‌ها پرداخت آنلاین یا انتقال بانکی انجام می‌شود.'],
        ['گارانتی محصولات به چه صورت است؟', 'همه کالاها با گارانتی شرکتی و فاکتور رسمی ارسال می‌شوند. مدت گارانتی هر کالا در جدول مشخصات محصول درج شده است.'],
        ['شرایط مرجوع کردن کالا چیست؟', 'تا ۷ روز پس از تحویل، در صورتی که بسته‌بندی کالا باز نشده و آسیب ندیده باشد، امکان بازگشت وجود دارد. هزینه بازگشت برای کالاهای معیوب بر عهده ما است.'],
        ['برای خرید عمده پروژه‌ای چه کنم؟', 'از طریق فرم تماس یا شماره تلفن پشتیبانی با ما در ارتباط باشید؛ کارشناس فروش فاکتور رسمی و شرایط ویژه پروژه‌ای را ارسال می‌کند.'],
        ['آیا نصب و راه‌اندازی هم انجام می‌دهید؟', 'نصب تجهیزات تهویه و پکیج توسط تیم همکار ما در تهران و کرج انجام می‌شود. برای سایر شهرها نصب‌کار معتبر معرفی می‌کنیم.'],
        ['اپلیکیشن شما را چطور نصب کنم؟', 'سایت قابلیت نصب (PWA) دارد؛ از منوی مرورگر گزینه «افزودن به صفحه اصلی» یا دکمه «نصب اپلیکیشن» در هدر سایت را انتخاب کنید.'],
    ];

    render('pages/faq', ['items' => $items], [
        'title'      => 'سؤالات متداول',
        'body_class' => 'page-faq',
    ]);
}

function terms(): void
{
    render('pages/terms', [], [
        'title'      => 'قوانین و مقررات',
        'body_class' => 'page-terms',
    ]);
}

function offline(): void
{
    render('pages/offline', [], [
        'title'    => 'حالت آفلاین',
        'no_index' => true,
    ]);
}
