/* ===========================================================================
   SazehShop — Service Worker (PWA)
   راهبردها:
   • صفحه‌ها: اول شبکه، در صورت آفلاین بودن از کش (سریع + همیشه به‌روز)
   • فایل‌های استاتیک: از کش با بروزرسانی پس‌زمینه‌ای
   • تصاویر: از کش، در صورت نبود از شبکه
   =========================================================================== */

const VERSION = 'v1';
const CACHE_STATIC = `sazehshop-static-${VERSION}`;
const CACHE_PAGES = `sazehshop-pages-${VERSION}`;
const CACHE_IMAGES = `sazehshop-images-${VERSION}`;

// مسیر نصب ممکن است در زیرپوشه باشد؛ همه آدرس‌ها نسبت به scope ساخته می‌شوند
const BASE = new URL(self.registration.scope).pathname.replace(/\/$/, '');
const withBase = (path) => BASE + path;

const PRECACHE = [
    withBase('/'),
    withBase('/offline.html'),
    withBase('/manifest.webmanifest'),
    withBase('/assets/css/app.css'),
    withBase('/assets/js/app.js'),
    withBase('/assets/js/pwa.js'),
    withBase('/assets/icons/icon-192.png'),
    withBase('/assets/icons/icon-512.png'),
    withBase('/assets/fonts/Vazirmatn-Regular.woff2'),
    withBase('/assets/fonts/Vazirmatn-SemiBold.woff2'),
    withBase('/assets/img/placeholder.svg')
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_STATIC)
            .then((cache) => cache.addAll(PRECACHE.map((url) => new Request(url, { cache: 'reload' }))))
            .catch(() => null)
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => ![CACHE_STATIC, CACHE_PAGES, CACHE_IMAGES].includes(key))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // --- درخواست‌های صفحه (navigation) ---
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_PAGES).then((cache) => cache.put(request, copy));
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(request);
                    if (cached) return cached;
                    const home = await caches.match(withBase('/'));
                    if (home) return home;
                    return caches.match(withBase('/offline.html'));
                })
        );
        return;
    }

    // --- تصاویر ---
    if (request.destination === 'image') {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_IMAGES).then((cache) => cache.put(request, copy));
                    return response;
                }).catch(() => caches.match(withBase('/assets/img/placeholder.svg')));
            })
        );
        return;
    }

    // --- سایر فایل‌های استاتیک: کش اول ---
    event.respondWith(
        caches.match(request).then((cached) => {
            const network = fetch(request).then((response) => {
                if (response && response.status === 200) {
                    const copy = response.clone();
                    caches.open(CACHE_STATIC).then((cache) => cache.put(request, copy));
                }
                return response;
            }).catch(() => cached);
            return cached || network;
        })
    );
});

// امکان بروزرسانی فوری از سمت صفحه
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') self.skipWaiting();
});
