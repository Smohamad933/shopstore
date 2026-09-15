<?php
/**
 * سؤالات متداول
 * @var array $items  [ [question, answer], ... ]
 */
?>
<div class="container container-narrow">
    <?php partial('breadcrumb', ['items' => [['label' => 'سؤالات متداول', 'url' => null]]]); ?>

    <h1 class="mb-2">سؤالات متداول</h1>

    <div class="surface" style="padding: 6px 20px;">
        <?php foreach ($items as $index => [$question, $answer]): ?>
            <details style="border-bottom:1px solid var(--line-2); padding:14px 0;" <?= $index === 0 ? 'open' : '' ?>>
                <summary style="cursor:pointer; font-weight:600; font-size:.95rem; list-style:none;">
                    <?= fa_digits($index + 1) ?>. <?= e($question) ?>
                </summary>
                <p class="small muted" style="margin:10px 0 0; line-height:2;"><?= e($answer) ?></p>
            </details>
        <?php endforeach; ?>
    </div>

    <div class="surface pad-lg mt-2 center">
        <h3 style="font-size:1rem;">پاسخ سؤالتان را پیدا نکردید؟</h3>
        <p class="small muted">کارشناسان ما در ساعات کاری پاسخگوی شما هستند.</p>
        <div class="row" style="justify-content:center;">
            <a class="btn btn-primary" href="<?= url('/contact') ?>">تماس با پشتیبانی</a>
            <a class="btn btn-ghost" href="<?= url('/track') ?>">پیگیری سفارش</a>
        </div>
    </div>
</div>
