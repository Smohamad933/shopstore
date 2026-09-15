<?php
/** صفحه‌بندی
 * @var array $pagination
 * @var array $query  پارامترهای اضافی برای حفظ در لینک‌ها
 */
$query = $query ?? [];
?>
<?php if (($pagination['last'] ?? 1) > 1): ?>
    <nav class="pagination" aria-label="صفحه‌بندی">
        <?php if ($pagination['page'] > 1): ?>
            <a href="<?= e(page_url($pagination['base'], $query, $pagination['page'] - 1)) ?>" aria-label="قبلی"><?= icon('chevron-r') ?></a>
        <?php endif; ?>

        <?php foreach ($pagination['links'] as $link): ?>
            <?php if ($link === '…'): ?>
                <span class="dots">…</span>
            <?php elseif ((int) $link === (int) $pagination['page']): ?>
                <span class="is-active"><?= fa_digits($link) ?></span>
            <?php else: ?>
                <a href="<?= e(page_url($pagination['base'], $query, (int) $link)) ?>"><?= fa_digits($link) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($pagination['page'] < $pagination['last']): ?>
            <a href="<?= e(page_url($pagination['base'], $query, $pagination['page'] + 1)) ?>" aria-label="بعدی"><?= icon('chevron-l') ?></a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
