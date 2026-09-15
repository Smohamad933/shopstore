<?php
/**
 * مدیریت آدرس‌ها
 * @var array $addresses
 * @var array $provinces
 */
?>
<div class="container">
    <h1 class="mb-2">آدرس‌های من</h1>

    <div class="account-layout">
        <?php partial('account-nav', ['user' => $user]); ?>

        <div>
            <?php if ($addresses === []): ?>
                <div class="alert alert-info"><?= icon('info') ?><span>هنوز آدرسی ثبت نکرده‌اید. با ثبت آدرس، تکمیل سفارش سریع‌تر می‌شود.</span></div>
            <?php else: ?>
                <div class="grid" style="grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; margin-bottom:16px;">
                    <?php foreach ($addresses as $address): ?>
                        <div class="surface pad">
                            <div class="row-between">
                                <b class="small"><?= e($address['title'] ?: 'آدرس') ?></b>
                                <?php if ((int) $address['is_default'] === 1): ?>
                                    <span class="badge badge-new">پیش‌فرض</span>
                                <?php endif; ?>
                            </div>
                            <div class="small mt-1"><?= e($address['receiver']) ?> — <?= fa_digits((string) $address['phone']) ?></div>
                            <div class="tiny muted"><?= e($address['province']) ?>، <?= e($address['city']) ?></div>
                            <div class="tiny muted"><?= e($address['address']) ?></div>
                            <?php if ($address['postal_code']): ?>
                                <div class="tiny muted">کد پستی: <?= fa_digits((string) $address['postal_code']) ?></div>
                            <?php endif; ?>

                            <form method="post" action="<?= url('/account/addresses/' . (int) $address['id'] . '/delete') ?>" class="mt-2"
                                  onsubmit="return confirm('این آدرس حذف شود؟')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-danger" type="submit"><?= icon('trash') ?> حذف آدرس</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="surface pad-lg">
                <h3 style="font-size:1rem;">افزودن آدرس جدید</h3>
                <form method="post" action="<?= url('/account/addresses') ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="field">
                            <label class="label" for="title">عنوان آدرس</label>
                            <input class="input" id="title" type="text" name="title" placeholder="مثلاً: دفتر کار">
                        </div>
                        <div class="field">
                            <label class="label" for="receiver">نام گیرنده</label>
                            <input class="input" id="receiver" type="text" name="receiver" required value="<?= e($user['name']) ?>">
                        </div>
                        <div class="field">
                            <label class="label" for="phone">شماره تماس</label>
                            <input class="input" id="phone" type="tel" name="phone" required inputmode="numeric" value="<?= e($user['phone']) ?>">
                        </div>
                        <div class="field">
                            <label class="label" for="province">استان</label>
                            <select class="select" id="province" name="province" required>
                                <option value="">انتخاب کنید…</option>
                                <?php foreach ($provinces as $province): ?>
                                    <option value="<?= e($province) ?>"><?= e($province) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="city">شهر</label>
                            <input class="input" id="city" type="text" name="city" required>
                        </div>
                        <div class="field">
                            <label class="label" for="postal_code">کد پستی</label>
                            <input class="input" id="postal_code" type="text" name="postal_code" inputmode="numeric">
                        </div>
                        <div class="field field-full">
                            <label class="label" for="address">نشانی کامل</label>
                            <textarea class="textarea" id="address" name="address" required style="min-height:90px;"></textarea>
                        </div>
                    </div>

                    <label class="checkbox mb-2">
                        <input type="checkbox" name="is_default" value="1">
                        <span>این آدرس به‌عنوان آدرس پیش‌فرض ثبت شود</span>
                    </label>

                    <button class="btn btn-primary" type="submit">ثبت آدرس</button>
                </form>
            </div>
        </div>
    </div>
</div>
