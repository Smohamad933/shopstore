<?php
/**
 * محافظت CSRF — همه فرم‌ها و درخواست‌های AJAX باید توکن داشته باشند
 */

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

/** فیلد مخفی برای فرم‌ها */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    $sent = (string) ($_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $stored = (string) ($_SESSION['csrf_token'] ?? '');
    return $stored !== '' && $sent !== '' && hash_equals($stored, $sent);
}

/** بررسی توکن از داخل کنترلرهای POST که خودشان خروجی JSON می‌دهند */
function csrf_verify_or_json(): void
{
    if (!csrf_check()) {
        json_response(['ok' => false, 'message' => 'نشست شما منقضی شده است. صفحه را رفرش کنید.'], 419);
    }
}
