/* ===========================================================================
   SazehShop — بخش PWA: ثبت سرویس‌ورکر، نصب اپلیکیشن و وضعیت آفلاین
   =========================================================================== */

(function () {
    'use strict';

    // --- ثبت سرویس‌ورکر ---
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            const base = (window.SHOP && window.SHOP.base) || '';
            navigator.serviceWorker.register(base + '/sw.js', { scope: base + '/' }).catch((error) => {
                console.warn('ثبت سرویس‌ورکر ناموفق بود:', error);
            });
        });
    }

    // --- دکمه نصب اپلیکیشن ---
    let deferredPrompt = null;
    const installButtons = () => document.querySelectorAll('[data-install-app]');

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        installButtons().forEach((button) => button.classList.remove('hidden'));
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-install-app]');
        if (!button) return;

        if (!deferredPrompt) {
            window.shopToast && window.shopToast('برای نصب، از منوی مرورگر گزینه «Add to Home Screen» را انتخاب کنید.', 'info');
            return;
        }

        deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        deferredPrompt = null;
        installButtons().forEach((item) => item.classList.add('hidden'));
        if (choice && choice.outcome === 'accepted' && window.shopToast) {
            window.shopToast('اپلیکیشن با موفقیت نصب شد. 🎉', 'success');
        }
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        installButtons().forEach((button) => button.classList.add('hidden'));
        window.shopToast && window.shopToast('اپلیکیشن روی صفحه اصلی اضافه شد.', 'success');
    });

    // --- نمایش وضعیت آفلاین ---
    const banner = document.getElementById('offlineBanner');
    function syncOnlineState() {
        if (!banner) return;
        banner.hidden = navigator.onLine;
    }
    window.addEventListener('online', syncOnlineState);
    window.addEventListener('offline', syncOnlineState);
    document.addEventListener('DOMContentLoaded', syncOnlineState);
    syncOnlineState();
})();
