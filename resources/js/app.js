import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* =============================================
   Smooth Eased Scroll Animation Helper
   easeInOutCubic curve provides a silky-smooth glide
   ============================================= */
window.smoothScrollTo = function(targetY = 0, duration = 650, callback = null) {
    const startY = window.pageYOffset || document.documentElement.scrollTop;
    const diff = targetY - startY;
    if (Math.abs(diff) < 2) {
        if (callback) callback();
        return;
    }
    
    let startTime = null;

    // Cubic Ease In-Out: gentle start, fast glide, cushioned finish
    function easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    }

    function step(currentTime) {
        if (!startTime) startTime = currentTime;
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const ease = easeInOutCubic(progress);

        window.scrollTo(0, startY + (diff * ease));

        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            window.scrollTo(0, targetY);
            if (callback) callback();
        }
    }

    requestAnimationFrame(step);
};

/* =============================================
   Smooth Anchor Navigation Helper (Hash links only)
   ============================================= */
document.addEventListener('DOMContentLoaded', () => {
    // Intercept clicks only on hash anchor links (#...) for smooth in-page scrolling
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href*="#"]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:') || anchor.target === '_blank') return;

        try {
            const targetUrl = new URL(href, window.location.href);

            // Only handle same-origin, same-page hash navigation
            if (targetUrl.origin === window.location.origin &&
                targetUrl.pathname === window.location.pathname &&
                targetUrl.hash) {
                const targetEl = document.querySelector(targetUrl.hash);
                if (targetEl) {
                    e.preventDefault();
                    const navOffset = window.innerWidth < 768 ? 92 : 80;
                    const elemPos = targetEl.getBoundingClientRect().top + window.pageYOffset;
                    const targetY = Math.max(0, elemPos - navOffset);
                    
                    window.smoothScrollTo(targetY, 700, () => {
                        history.pushState(null, '', targetUrl.hash);
                    });
                }
            }
        } catch (_) {}
    });
});

/* =============================================
   Scroll Reveal — Intersection Observer
   Watches elements with [data-reveal] attribute
   and adds .revealed class when they enter the viewport
   ============================================= */
(function initScrollReveal() {
    const REVEAL_ATTR = 'data-reveal';
    const REVEALED_CLASS = 'revealed';

    function revealAllNow() {
        document.querySelectorAll(`[${REVEAL_ATTR}]`).forEach((el) => {
            el.classList.add(REVEALED_CLASS);
        });
    }

    if (!('IntersectionObserver' in window)) {
        revealAllNow();
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const delay = el.dataset.revealDelay || '0';
                el.style.transitionDelay = delay + 'ms';
                el.classList.add(REVEALED_CLASS);
                observer.unobserve(el);
            }
        });
    }, {
        threshold: 0.05,
        rootMargin: '0px 0px -20px 0px',
    });

    function observeAll() {
        document.querySelectorAll(`[${REVEAL_ATTR}]`).forEach((el) => {
            if (!el.classList.contains(REVEALED_CLASS)) {
                observer.observe(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', observeAll);
    } else {
        observeAll();
    }

    window.addEventListener('alpine:initialized', observeAll);
    window.addEventListener('scroll-reveal:refresh', observeAll);

    // If loaded with a hash in URL (e.g. #features), ensure elements in view reveal quickly
    if (window.location.hash) {
        setTimeout(observeAll, 100);
    }
})();
