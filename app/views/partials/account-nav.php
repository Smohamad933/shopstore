<?php
/** منوی کناری پنل کاربری */
$path = request_path();
$links = [
    ['/account', 'پیشخوان', 'home'],
    ['/account/orders', 'سفارش‌های من', 'package'],
    ['/account/addresses', 'آدرس‌ها', 'pin'],
    ['/account/profile', 'اطلاعات حساب', 'user'],
];
?>
<aside class="account-nav">
    <div class="account-user">
        <div class="row">
            <span class="avatar"><?= e(mb_substr((string) $user['name'], 0, 1)) ?></span>
            <div class="grow">
                <b><?= e($user['name']) ?></b>
                <div class="tiny muted"><?= e($user['email']) ?></div>
            </div>
        </div>
    </div>
    <?php foreach ($links as [$href, $label, $iconName]): ?>
        <a href="<?= url($href) ?>" class="<?= $path === $href ? 'is-active' : '' ?>">
            <?= icon($iconName) ?> <?= e($label) ?>
        </a>
    <?php endforeach; ?>
    <a href="<?= url('/logout') ?>"><?= icon('logout') ?> خروج از حساب</a>
</aside>
