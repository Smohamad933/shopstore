<?php
/** پیام‌های موقت بعد از عملیات‌ها */
if (empty($flashItems)) {
    return;
}
?>
<div class="container flash-stack">
    <?php foreach ($flashItems as $item): ?>
        <div class="alert alert-<?= e($item['type'] === 'success' ? 'success' : ($item['type'] === 'warn' ? 'warn' : 'error')) ?>">
            <?= icon($item['type'] === 'success' ? 'check' : 'info') ?>
            <span><?= e($item['message']) ?></span>
        </div>
    <?php endforeach; ?>
</div>
