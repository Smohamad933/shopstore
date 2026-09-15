<?php /** تماس با ما */ ?>
<div class="container">
    <?php partial('breadcrumb', ['items' => [['label' => 'تماس با ما', 'url' => null]]]); ?>

    <div class="grid" style="grid-template-columns: 340px minmax(0,1fr); gap:20px; align-items:start;">
        <div class="surface pad-lg">
            <h3 style="font-size:1rem;">راه‌های ارتباطی</h3>
            <ul class="footer-contact">
                <li><?= icon('phone') ?><div><b class="small">تلفن فروش</b><br><a href="tel:<?= e(en_digits(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></div></li>
                <li><?= icon('phone') ?><div><b class="small">موبایل / واتس‌اپ</b><br><a href="<?= e(setting('whatsapp')) ?>"><?= e(setting('mobile')) ?></a></div></li>
                <li><?= icon('mail') ?><div><b class="small">ایمیل</b><br><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div></li>
                <li><?= icon('pin') ?><div><b class="small">نشانی فروشگاه</b><br><?= e(setting('address')) ?></div></li>
                <li><?= icon('clock') ?><div><b class="small">ساعات کاری</b><br><?= e(setting('work_hours')) ?></div></li>
            </ul>

            <div class="alert alert-info mb-0">
                <?= icon('info') ?>
                <span>برای استعلام قیمت پروژه‌ای، لیست اقلام و متراژ را در متن پیام بنویسید تا سریع‌تر پاسخ بگیرید.</span>
            </div>
        </div>

        <div class="surface pad-lg">
            <h3 style="font-size:1rem;">ارسال پیام</h3>
            <form method="post" action="<?= url('/contact') ?>">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="name">نام و نام خانوادگی <span class="req">*</span></label>
                        <input class="input" id="name" type="text" name="name" required>
                    </div>
                    <div class="field">
                        <label class="label" for="phone">شماره تماس</label>
                        <input class="input" id="phone" type="tel" name="phone" inputmode="numeric">
                    </div>
                    <div class="field">
                        <label class="label" for="email">ایمیل</label>
                        <input class="input" id="email" type="email" name="email">
                    </div>
                    <div class="field">
                        <label class="label" for="subject">موضوع</label>
                        <input class="input" id="subject" type="text" name="subject" placeholder="مثلاً: استعلام قیمت پکیج">
                    </div>
                    <div class="field field-full">
                        <label class="label" for="message">متن پیام <span class="req">*</span></label>
                        <textarea class="textarea" id="message" name="message" required
                                  placeholder="اقلام مورد نیاز، متراژ پروژه یا سؤال فنی خود را بنویسید…"></textarea>
                    </div>
                </div>
                <button class="btn btn-primary btn-lg" type="submit">ارسال پیام</button>
            </form>
        </div>
    </div>
</div>
