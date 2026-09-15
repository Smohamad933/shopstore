<?php
/** پیام‌های فرم تماس */

declare(strict_types=1);

function index(): void
{
    require_admin();

    render_admin('admin/messages', [
        'messages' => db_all('SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC LIMIT 100'),
    ], ['title' => 'پیام‌ها']);
}

function mark_read(string $id): void
{
    require_admin();

    db_update('contact_messages', ['is_read' => 1], 'id = :id', ['id' => (int) $id]);
    flash_success('پیام خوانده‌شده علامت زد.');
    redirect('/admin/messages');
}
