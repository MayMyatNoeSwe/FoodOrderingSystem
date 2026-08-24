import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* =============================================
   Page Transition — fade out on link navigation
   ============================================= */
document.addEventListener('DOMContentLoaded', () => {
    // Fade page out before navigating away to a different page
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        // Skip empty, hash, javascript, mailto, tel, target _blank, download, or form buttons
        if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
            href.startsWith('mailto:') || href.startsWith('tel:') ||
            anchor.target === '_blank' || anchor.hasAttribute('download') ||
            anchor.closest('form')) return;

        try {
            const targetUrl = new URL(href, window.location.href);

            // Skip external links
            if (targetUrl.origin !== window.location.origin) return;

            // Handle same-page hash anchors (e.g. /#features, /#categories, /#menu when on /)
            if (targetUrl.pathname === window.location.pathname) {
                if (targetUrl.hash) {
                    const targetEl = document.querySelector(targetUrl.hash);
                    if (targetEl) {
                        e.preventDefault();
                        targetEl.scrollIntoView({ behavior: 'smooth' });
                        history.pushState(null, '', targetUrl.hash);
                    }
                    return;
                }
                // Same exact URL clicked, no need to transition
                if (targetUrl.search === window.location.search && targetUrl.href === window.location.href) {
                    e.preventDefault();
                    return;
                }
            }
        } catch (_) { return; }

        // Trigger smooth fade-out only when navigating to another route
        document.documentElement.classList.add('page-leaving');

        e.preventDefault();
        setTimeout(() => { window.location.href = href; }, 220);
    });
});

// Restore visibility if restored from browser back/forward cache (bfcache)
window.addEventListener('pageshow', () => {
    document.documentElement.classList.remove('page-leaving');
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
