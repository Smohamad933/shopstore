<?php
/**
 * آیکون‌های SVG درون‌خطی (بدون فونت آیکون و بدون درخواست شبکه‌ای)
 * استفاده: <?= icon('cart') ?>
 */

declare(strict_types=1);

function icon(string $name, string $class = ''): string
{
    static $icons = [
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'cart'      => '<path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.55L21 8H6"/><circle cx="10" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/>',
        'user'      => '<circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c1.2-3.4 4-5.2 7.5-5.2S18.3 16.6 19.5 20"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
        'home'      => '<path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1z"/>',
        'grid'      => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
        'phone'     => '<path d="M6 3h3l1.5 4-2 1.5a12 12 0 0 0 5 5L15 11.5 19 13v3a2 2 0 0 1-2.2 2A15 15 0 0 1 4 5.2 2 2 0 0 1 6 3z"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
        'pin'       => '<path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
        'truck'     => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
        'shield'    => '<path d="M12 3.5 19 6v6c0 4.2-3 7-7 8.5-4-1.5-7-4.3-7-8.5V6z"/><path d="m9 12 2 2 4-4"/>',
        'headset'   => '<path d="M5 13a7 7 0 0 1 14 0"/><rect x="3" y="13" width="4" height="6" rx="1.6"/><rect x="17" y="13" width="4" height="6" rx="1.6"/><path d="M19 19a3 3 0 0 1-3 2.5h-2"/>',
        'tag'       => '<path d="M20 12.5 12.5 20 4 11.5V4h7.5z"/><circle cx="8" cy="8" r="1.4"/>',
        'chevron'   => '<path d="m14 7-5 5 5 5"/>',
        'chevron-l' => '<path d="m14 7-5 5 5 5"/>',
        'chevron-r' => '<path d="m10 7 5 5-5 5"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'minus'     => '<path d="M5 12h14"/>',
        'trash'     => '<path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13"/>',
        'check'     => '<path d="m5 12.5 4.5 4.5L19 7"/>',
        'star'      => '<path d="m12 4 2.4 5 5.6.8-4 4 1 5.6-5-2.8-5 2.8 1-5.6-4-4 5.6-.8z"/>',
        'filter'    => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'package'   => '<path d="M12 3 20 7.2v9.6L12 21l-8-4.2V7.2z"/><path d="M4 7.2 12 11.5l8-4.3M12 11.5V21"/>',
        'chart'     => '<path d="M4 20V6M10 20V10M16 20v-7M22 20H2"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.5M12 18.5V21M4.9 7l2.2 1.3M16.9 15.7l2.2 1.3M4.9 17l2.2-1.3M16.9 8.3 19.1 7"/>',
        'logout'    => '<path d="M15 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8"/><path d="M17 9l3 3-3 3M20 12h-8"/>',
        'edit'      => '<path d="M5 19h3l9.5-9.5-3-3L5 16z"/><path d="m15.5 5.5 3 3"/>',
        'eye'       => '<path d="M2.5 12S6 6.5 12 6.5S21.5 12 21.5 12S18 17.5 12 17.5S2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.6"/>',
        'download'  => '<path d="M12 4v11"/><path d="m8 11 4 4 4-4"/><path d="M5 19h14"/>',
        'wifi-off'  => '<path d="M3 3l18 18M8.5 15.5a5 5 0 0 1 7 0M5.5 12a11 11 0 0 1 3.3-2M18.5 12a11 11 0 0 0-2.2-1.4M11.4 19.5a1 1 0 0 1 1.2 0"/>',
        'heart'     => '<path d="M12 20s-7-4.4-7-9.3A4.2 4.2 0 0 1 12 8a4.2 4.2 0 0 1 7 2.7C19 15.6 12 20 12 20z"/>',
        'clock'     => '<circle cx="12" cy="12" r="8"/><path d="M12 8v4.2l3 1.8"/>',
        'bolt'      => '<path d="M13 3 6 13h5l-1 8 7-10h-5z"/>',
        'info'      => '<circle cx="12" cy="12" r="8.5"/><path d="M12 11v5.5M12 8h.01"/>',
        'instagram' => '<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.6"/><circle cx="16.6" cy="7.4" r=".9" fill="currentColor" stroke="none"/>',
        'telegram'  => '<path d="M21 5 3.5 11.5l4.6 1.6L9.5 20l2.8-3.6 4.4 3.1z"/>',
        'whatsapp'  => '<path d="M20.5 12a8.5 8.5 0 0 1-12.6 7.5L4 20.5l1.2-3.6A8.5 8.5 0 1 1 20.5 12z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5"/>',
    ];

    $paths = $icons[$name] ?? $icons['info'];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}
