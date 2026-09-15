<?php
/**
 * پیام‌های موقت (Flash) — بعد از ریدایرکت یک بار نمایش داده می‌شوند
 */

declare(strict_types=1);

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_success(string $message): void
{
    flash('success', $message);
}

function flash_error(string $message): void
{
    flash('error', $message);
}

/** خواندن و پاک کردن پیام‌ها */
function flash_pull(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($messages) ? $messages : [];
}

/** نگه‌داشتن مقادیر فرم پس از خطای اعتبارسنجی */
function old(string $key, mixed $default = ''): string
{
    $value = $_SESSION['old'][$key] ?? $default;
    return is_scalar($value) ? (string) $value : '';
}

function old_keep(array $data): void
{
    unset($data['_token'], $data['password'], $data['password_confirmation']);
    $_SESSION['old'] = $data;
}

function old_clear(): void
{
    unset($_SESSION['old']);
}
