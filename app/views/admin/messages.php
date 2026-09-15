<?php
/**
 * پیام‌های فرم تماس
 * @var array $messages
 */
?>
<div class="card">
    <div class="card-head">
        <h3>پیام‌های مشتریان</h3>
        <span class="muted small"><?= fa_digits(count($messages)) ?> پیام</span>
    </div>

    <?php if ($messages === []): ?>
        <div class="empty-admin">پیامی دریافت نشده است.</div>
    <?php else: ?>
        <div class="card-body">
            <?php foreach ($messages as $message): ?>
                <div style="border:1px solid <?= (int) $message['is_read'] === 1 ? 'var(--line-2)' : 'var(--line)' ?>; border-radius:var(--radius-sm); padding:14px; margin-bottom:10px; background:<?= (int) $message['is_read'] === 1 ? '#fff' : 'var(--surface-2)' ?>;">
                    <div class="row-between wrap">
                        <div class="row">
                            <span class="avatar"><?= e(mb_substr($message['name'], 0, 1)) ?></span>
                            <div>
                                <b class="small"><?= e($message['name']) ?></b>
                                <?php if ((int) $message['is_read'] === 0): ?><span class="pill pill-warn">جدید</span><?php endif; ?>
                                <div class="tiny muted">
                                    <?= e($message['subject']) ?>
                                    <?= $message['phone'] ? ' • ' . fa_digits((string) $message['phone']) : '' ?>
                                    <?= $message['email'] ? ' • ' . e($message['email']) : '' ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <span class="tiny muted"><?= e(jdate($message['created_at'], 'date_time')) ?></span>
                            <?php if ((int) $message['is_read'] === 0): ?>
                                <form method="post" action="<?= url('/admin/messages/' . (int) $message['id'] . '/read') ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm" type="submit">علامت‌گذاری خوانده‌شده</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="small mb-0 mt-1" style="line-height:2;"><?= nl2br(e($message['message'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
