/* ===========================================================================
   SazehShop — اسکریپت اصلی سایت
   بدون هیچ کتابخانه خارجی؛ همه تعامل‌ها با fetch و DOM ساده انجام می‌شود.
   =========================================================================== */

(function () {
    'use strict';

    const SHOP = window.SHOP || {};
    const faDigits = (value) => String(value).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
    const groupDigits = (value) => faDigits(Number(value).toLocaleString('en-US').replace(/,/g, '٬'));

    /* -------------------------------------------------------------- ابزارها */

    async function post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': SHOP.csrf || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data || {}),
            credentials: 'same-origin'
        });
        let payload = {};
        try {
            payload = await response.json();
        } catch (error) {
            payload = { ok: false, message: 'پاسخ سرور قابل خواندن نبود.' };
        }
        return payload;
    }

    function toast(message, type = 'info') {
        const stack = document.getElementById('toastStack');
        if (!stack) return;
        const element = document.createElement('div');
        element.className = 'toast is-' + type;
        element.textContent = message;
        stack.appendChild(element);
        setTimeout(() => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(8px)';
            element.style.transition = 'all .25s ease';
            setTimeout(() => element.remove(), 260);
        }, 3800);
    }
    window.shopToast = toast;

    function updateCartBadge(count) {
        document.querySelectorAll('[data-cart-count]').forEach((badge) => {
            badge.textContent = faDigits(count);
            badge.classList.toggle('hidden', Number(count) <= 0);
        });
    }

    /* ------------------------------------------------------- منوی کشویی موبایل */

    function initDrawer() {
        const drawer = document.getElementById('drawer');
        if (!drawer) return;
        const open = () => {
            drawer.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };
        const close = () => {
            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        document.querySelectorAll('[data-drawer-open]').forEach((btn) => btn.addEventListener('click', open));
        drawer.querySelectorAll('[data-drawer-close]').forEach((btn) => btn.addEventListener('click', close));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
    }

    /* --------------------------------------------------------- فیلتر موبایل */

    function initFilters() {
        const panel = document.querySelector('.filters');
        const toggle = document.querySelector('[data-filter-toggle]');
        if (!panel || !toggle) return;

        toggle.addEventListener('click', () => {
            panel.classList.toggle('is-open');
            document.body.style.overflow = panel.classList.contains('is-open') ? 'hidden' : '';
        });

        panel.addEventListener('click', (event) => {
            if (event.target.closest('[data-filter-close]')) {
                panel.classList.remove('is-open');
                document.body.style.overflow = '';
            }
        });
    }

    /* ------------------------------------------------------------ افزودن به سبد */

    function initAddToCart() {
        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-add-to-cart]');
            if (!button) return;
            event.preventDefault();

            const productId = Number(button.dataset.addToCart);
            const qtyInput = document.querySelector('[data-product-qty]');
            const qty = button.hasAttribute('data-use-qty') && qtyInput ? Number(qtyInput.value) || 1 : 1;

            button.classList.add('is-loading');
            const result = await post(SHOP.urls.cartAdd, { id: productId, qty: qty });
            button.classList.remove('is-loading');

            if (result.ok) {
                toast(result.message || 'به سبد خرید اضافه شد.', 'success');
                if (typeof result.count !== 'undefined') updateCartBadge(result.count);
                button.classList.add('is-done');
                setTimeout(() => button.classList.remove('is-done'), 1200);
            } else {
                toast(result.message || 'افزودن به سبد خرید انجام نشد.', 'error');
            }
        });
    }

    /* ------------------------------------------------------------- سبد خرید */

    function renderCart(payload) {
        if (!payload || !payload.totals) return;

        updateCartBadge(payload.count);

        document.querySelectorAll('[data-summary-subtotal]').forEach((el) => (el.textContent = payload.totals.subtotal));
        document.querySelectorAll('[data-summary-discount]').forEach((el) => {
            el.textContent = payload.totals.discount;
            const row = el.closest('[data-discount-row]');
            if (row) row.classList.toggle('hidden', payload.totals.discount === '۰');
        });
        document.querySelectorAll('[data-summary-shipping]').forEach((el) => (el.textContent = payload.totals.shipping));
        document.querySelectorAll('[data-summary-total]').forEach((el) => (el.textContent = payload.totals.total));
    }

    function initCartPage() {
        const page = document.querySelector('[data-cart-page]');
        if (!page) return;

        async function changeQty(productId, qty) {
            const result = await post(SHOP.urls.cartUpdate, { id: productId, qty: qty });
            if (!result.ok) {
                toast(result.message || 'بروزرسانی سبد خرید انجام نشد.', 'error');
                return;
            }

            const row = document.querySelector('[data-cart-row="' + productId + '"]');
            const item = (result.items || []).find((entry) => Number(entry.id) === Number(productId));

            if (row) {
                if (!item || Number(qty) <= 0) {
                    row.remove();
                } else {
                    const input = row.querySelector('[data-qty-input]');
                    const line = row.querySelector('[data-line-total]');
                    if (input) input.value = item.qty;
                    if (line) line.textContent = item.line_total;
                }
            }

            if (result.message) toast(result.message, 'success');
            renderCart(result);

            if (!document.querySelector('[data-cart-row]')) {
                window.location.reload();
            }
        }

        page.addEventListener('click', (event) => {
            const plus = event.target.closest('[data-qty-plus]');
            const minus = event.target.closest('[data-qty-minus]');
            const remove = event.target.closest('[data-cart-remove]');

            if (plus || minus || remove) {
                const holder = event.target.closest('[data-cart-row]');
                if (!holder) return;
                const productId = Number(holder.dataset.cartRow);
                const input = holder.querySelector('[data-qty-input]');
                const current = Number(input ? input.value : 1);

                if (remove) return void changeQty(productId, 0);
                return void changeQty(productId, plus ? current + 1 : current - 1);
            }
        });

        page.addEventListener('change', (event) => {
            const input = event.target.closest('[data-qty-input]');
            if (!input) return;
            const holder = input.closest('[data-cart-row]');
            if (!holder) return;
            changeQty(Number(holder.dataset.cartRow), Number(input.value) || 1);
        });

        const couponForm = page.querySelector('[data-coupon-form]');
        if (couponForm) {
            couponForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const input = couponForm.querySelector('input[name="code"]');
                const button = couponForm.querySelector('button[type="submit"]');
                button.classList.add('is-loading');
                const result = await post(SHOP.urls.cartCoupon || (SHOP.base + '/cart/coupon'), { code: input.value });
                button.classList.remove('is-loading');
                toast(result.message, result.ok ? 'success' : 'error');
                if (result.ok) {
                    renderCart(result);
                    setTimeout(() => window.location.reload(), 700);
                }
            });
        }
    }

    /* --------------------------------------------------- پیشنهاد جست‌وجو */

    function initSearch() {
        document.querySelectorAll('[data-search-form]').forEach((form) => {
            const input = form.querySelector('input[name="q"]');
            const box = form.querySelector('.suggestions');
            if (!input || !box) return;

            let timer = null;

            input.addEventListener('input', () => {
                const term = input.value.trim();
                clearTimeout(timer);
                if (term.length < 2) {
                    box.hidden = true;
                    return;
                }
                timer = setTimeout(async () => {
                    try {
                        const response = await fetch(SHOP.urls.search + '?q=' + encodeURIComponent(term), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        if (!data.items || !data.items.length) {
                            box.hidden = true;
                            return;
                        }
                        box.innerHTML = data.items.map((item) =>
                            '<a href="' + item.url + '">' +
                            '<img src="' + item.image + '" alt="">' +
                            '<span class="grow">' + item.name + '<br><small class="muted">' + item.price + ' تومان</small></span>' +
                            '</a>'
                        ).join('');
                        box.hidden = false;
                    } catch (error) {
                        box.hidden = true;
                    }
                }, 260);
            });

            document.addEventListener('click', (event) => {
                if (!form.contains(event.target)) box.hidden = true;
            });
        });
    }

    /* ------------------------------------------------------- گالری و تب‌ها */

    function initGallery() {
        const gallery = document.querySelector('[data-gallery]');
        if (!gallery) return;
        const main = gallery.querySelector('[data-gallery-main] img');

        gallery.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
            thumb.addEventListener('click', () => {
                gallery.querySelectorAll('[data-gallery-thumb]').forEach((item) => item.classList.remove('is-active'));
                thumb.classList.add('is-active');
                const source = thumb.querySelector('img');
                if (main && source) main.src = source.src.replace(/-\d+x\d+(?=\.)/, '');
            });
        });
    }

    function initTabs() {
        document.querySelectorAll('[data-tabs]').forEach((tabs) => {
            const buttons = tabs.querySelectorAll('[data-tab]');
            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.dataset.tab;
                    buttons.forEach((item) => item.classList.toggle('is-active', item === button));
                    tabs.querySelectorAll('[data-tab-panel]').forEach((panel) => {
                        panel.classList.toggle('is-active', panel.dataset.tabPanel === target);
                    });
                });
            });
        });
    }

    /* ------------------------------------------------------ شمارنده تخفیف */

    function initCountdowns() {
        const nodes = document.querySelectorAll('[data-countdown]');
        if (!nodes.length) return;

        function tick() {
            nodes.forEach((node) => {
                const target = new Date(node.dataset.countdown.replace(' ', 'T')).getTime();
                const diff = target - Date.now();

                if (isNaN(target) || diff <= 0) {
                    node.querySelectorAll('[data-cd]').forEach((part) => (part.textContent = '۰'));
                    return;
                }

                const seconds = Math.floor(diff / 1000);
                const parts = {
                    days: Math.floor(seconds / 86400),
                    hours: Math.floor((seconds % 86400) / 3600),
                    minutes: Math.floor((seconds % 3600) / 60),
                    seconds: seconds % 60
                };
                Object.keys(parts).forEach((key) => {
                    const element = node.querySelector('[data-cd="' + key + '"]');
                    if (element) element.textContent = faDigits(String(parts[key]).padStart(2, '0'));
                });
            });
        }

        tick();
        setInterval(tick, 1000);
    }

    /* ---------------------------------------------------------- اسلایدر هیرو */

    function initHero() {
        const hero = document.querySelector('[data-hero]');
        if (!hero) return;

        const slides = Array.from(hero.querySelectorAll('.hero-slide'));
        const dots = Array.from(hero.querySelectorAll('[data-hero-dot]'));
        if (slides.length < 2) return;

        let index = 0;
        let timer = null;

        function show(next) {
            index = (next + slides.length) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
            dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
        }

        function play() {
            clearInterval(timer);
            timer = setInterval(() => show(index + 1), 6500);
        }

        dots.forEach((dot, i) => dot.addEventListener('click', () => {
            show(i);
            play();
        }));

        play();
    }

    /* ------------------------------------------------------- شمارنده تعداد */

    function initQtySteppers() {
        document.addEventListener('click', (event) => {
            const step = event.target.closest('[data-step]');
            if (!step) return;
            const wrapper = step.closest('.qty');
            const input = wrapper ? wrapper.querySelector('input') : null;
            if (!input) return;
            const min = Number(input.min || 1);
            const max = Number(input.max || 99);
            const next = (Number(input.value) || min) + (step.dataset.step === 'up' ? 1 : -1);
            input.value = Math.max(min, Math.min(max, next));
        });
    }

    /* --------------------------------------------------------- تب‌های مرتب‌سازی (موبایل) */

    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const target = document.querySelector(link.getAttribute('href'));
                if (!target) return;
                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    /* ------------------------------------------------------------- راه‌اندازی */

    document.addEventListener('DOMContentLoaded', () => {
        initDrawer();
        initFilters();
        initAddToCart();
        initCartPage();
        initSearch();
        initGallery();
        initTabs();
        initCountdowns();
        initHero();
        initQtySteppers();
        initSmoothScroll();
    });
})();
