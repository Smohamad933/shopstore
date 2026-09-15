<?php
/** مسیر راهنما (Breadcrumb)
 * @var array $items  [ ['label' => '...', 'url' => '/...'|null], ... ]
 */
?>
<nav class="breadcrumb" aria-label="مسیر صفحه">
    <a href="<?= url('/') ?>">خانه</a>
    <?php foreach ($items as $item): ?>
        <span class="sep"><?= icon('chevron') ?></span>
        <?php if (!empty($item['url'])): ?>
            <a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a>
        <?php else: ?>
            <span><?= e($item['label']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
