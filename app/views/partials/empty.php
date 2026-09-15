<?php
/** وضعیت خالی
 * @var string $title
 * @var string $text
 * @var string|null $actionLabel
 * @var string|null $actionUrl
 * @var string $icon
 */
?>
<div class="empty-state">
    <?= icon($icon ?? 'package') ?>
    <h3><?= e($title) ?></h3>
    <p class="muted small mb-2"><?= e($text) ?></p>
    <?php if (!empty($actionLabel) && !empty($actionUrl)): ?>
        <a class="btn btn-primary" href="<?= e($actionUrl) ?>"><?= e($actionLabel) ?></a>
    <?php endif; ?>
</div>
