<?php
/**
 * تنظیمات فروشگاه
 * @var array $grouped
 * @var array $labels
 * @var array $longFields
 */
$toggles = ['online_enabled', 'cod_enabled'];
?>
<form class="admin-form" method="post" action="<?= url('/admin/settings') ?>">
    <?= csrf_field() ?>

    <div class="grid" style="grid-template-columns: repeat(2, minmax(0,1fr)); gap:16px;">
        <?php foreach ($grouped as $groupTitle => $keys): ?>
            <div class="card">
                <div class="card-head"><h3><?= e($groupTitle) ?></h3></div>
                <div class="card-body">
                    <?php foreach ($keys as $key): ?>
                        <?php
                        $label = $labels[$key] ?? $key;
                        $value = setting($key);
                        $isLong = in_array($key, $longFields, true);
                        ?>
                        <?php if (in_array($key, $toggles, true)): ?>
                            <label class="checkbox mb-2">
                                <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $value === '1' ? 'checked' : '' ?>>
                                <span><?= e($label) ?></span>
                            </label>
                        <?php elseif ($key === 'gateway'): ?>
                            <div class="field">
                                <label class="label" for="<?= e($key) ?>"><?= e($label) ?></label>
                                <select class="select" id="<?= e($key) ?>" name="<?= e($key) ?>">
                                    <option value="mock" <?= $value === 'mock' ? 'selected' : '' ?>>شبیه‌ساز (حالت آزمایشی)</option>
                                    <option value="zarinpal" <?= $value === 'zarinpal' ? 'selected' : '' ?>>زرین‌پال</option>
                                    <option value="idpay" <?= $value === 'idpay' ? 'selected' : '' ?>>آیدی‌پی</option>
                                </select>
                                <div class="hint">برای اتصال واقعی، کد درگاه در app/controllers/checkout.php جایگزین می‌شود (محل مشخص‌شده در کامنت‌ها).</div>
                            </div>
                        <?php else: ?>
                            <div class="field">
                                <label class="label" for="<?= e($key) ?>"><?= e($label) ?></label>
                                <?php if ($isLong): ?>
                                    <textarea class="textarea" id="<?= e($key) ?>" name="<?= e($key) ?>" style="min-height:90px;"><?= e($value) ?></textarea>
                                <?php else: ?>
                                    <input class="input" id="<?= e($key) ?>" type="text" name="<?= e($key) ?>" value="<?= e($value) ?>"
                                           <?= str_starts_with($key, 'shipping') || str_contains($key, 'from') ? 'inputmode="numeric"' : '' ?>>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row mt-2">
        <button class="btn btn-primary" type="submit">ذخیره تنظیمات</button>
        <a class="btn btn-ghost" href="<?= url('/admin') ?>">انصراف</a>
    </div>
</form>
