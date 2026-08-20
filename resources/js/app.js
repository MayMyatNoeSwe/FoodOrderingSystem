import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* =============================================
   Page Transition — fade out on link navigation
   ============================================= */
document.addEventListener('DOMContentLoaded', () => {
    // Fade page in
    document.documentElement.classList.add('page-ready');

    // Fade page out before navigating away
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        // Skip external, hash, js, and form-action links
        if (!href || href.startsWith('#') || href.startsWith('javascript') ||
            href.startsWith('mailto') || href.startsWith('tel') ||
            anchor.target === '_blank' || anchor.hasAttribute('download') ||
            anchor.closest('form')) return;

        // Skip same-page anchors
        try {
            const url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) return;
        } catch (_) { return; }

        e.preventDefault();
        document.documentElement.classList.add('page-leaving');

        setTimeout(() => { window.location.href = href; }, 280);
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
        threshold: 0.08,
        rootMargin: '0px 0px -40px 0px',
    });

    function observeAll() {
        document.querySelectorAll(`[${REVEAL_ATTR}]`).forEach((el) => {
            if (!el.classList.contains(REVEALED_CLASS)) {
                observer.observe(el);
            }
        });
    }

    // Run on DOMContentLoaded and also after Alpine/dynamic content
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', observeAll);
    } else {
        observeAll();
    }

    // Re-run after any Alpine x-init / dynamic inserts
    window.addEventListener('alpine:initialized', observeAll);
    window.addEventListener('scroll-reveal:refresh', observeAll);
})();
