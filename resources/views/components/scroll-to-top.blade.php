<div x-data="{
        showScrollTop: false,
        scrollPercent: 0,
        isScrolling: false,
        updateScroll() {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            this.showScrollTop = winScroll > 220;
            this.scrollPercent = height > 0 ? Math.min(100, Math.round((winScroll / height) * 100)) : 0;
        },
        scrollToTop() {
            if (this.isScrolling) return;
            this.isScrolling = true;
            if (typeof window.smoothScrollTo === 'function') {
                window.smoothScrollTo(0, 750, () => {
                    this.isScrolling = false;
                });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setTimeout(() => { this.isScrolling = false; }, 750);
            }
        }
    }"
    @scroll.window="updateScroll()"
    x-init="updateScroll()"
    class="fixed bottom-6 right-6 z-40"
    style="display: none;"
    x-show="showScrollTop"
    x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-400 transform"
    x-transition:enter-start="opacity-0 translate-y-8 scale-50"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition cubic-bezier(0.4, 0, 0.2, 1) duration-250 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-50">

    <!-- Ambient Glowing Pulse Halo -->
    <div class="pointer-events-none absolute -inset-1.5 rounded-full bg-gradient-to-r from-orange-500/30 to-amber-500/30 dark:from-orange-500/20 dark:to-rose-500/20 blur-md animate-pulse-halo"
         x-show="scrollPercent > 10"></div>

    <button
        @click="scrollToTop()"
        type="button"
        title="{{ __('Scroll to Top') }}"
        aria-label="{{ __('Scroll to Top') }}"
        :class="{ 'ring-4 ring-orange-500/30 scale-95': isScrolling }"
        class="group relative flex items-center justify-center w-12 h-12 rounded-full bg-white/95 dark:bg-slate-900/95 text-slate-800 dark:text-white shadow-xl shadow-slate-950/10 dark:shadow-black/50 border border-slate-200/90 dark:border-slate-700/90 backdrop-blur-xl hover:border-orange-500/60 dark:hover:border-orange-500/60 hover:shadow-2xl hover:shadow-orange-500/25 active:scale-90 hover:-translate-y-1 transition-all duration-300 cursor-pointer">

        <!-- Circular Scroll Progress Ring SVG -->
        <svg class="absolute inset-0 w-full h-full -rotate-90 pointer-events-none p-0.5" viewBox="0 0 44 44">
            <!-- Background Track -->
            <circle
                cx="22" cy="22" r="19"
                class="stroke-slate-200/80 dark:stroke-slate-800/90"
                stroke-width="2.5"
                fill="none"
            />
            <!-- Dynamic Progress Fill -->
            <circle
                cx="22" cy="22" r="19"
                class="stroke-orange-500 dark:stroke-orange-400 transition-all duration-150 ease-out"
                stroke-width="2.5"
                stroke-linecap="round"
                fill="none"
                stroke-dasharray="119.38"
                :stroke-dashoffset="119.38 - (119.38 * scrollPercent) / 100"
            />
        </svg>

        <!-- Upward Arrow Icon with Micro-Animation -->
        <div class="relative z-10 flex items-center justify-center text-orange-500 group-hover:text-orange-600 dark:text-orange-400 transition-all duration-300"
             :class="{ 'animate-arrow-launch': isScrolling, 'group-hover:-translate-y-0.5': !isScrolling }">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
            </svg>
        </div>

        <!-- Floating Hover Tooltip with % Indicator -->
        <span class="absolute -top-9 left-1/2 -translate-x-1/2 px-2.5 py-1 bg-slate-900/95 dark:bg-slate-800/95 backdrop-blur-md text-white text-[10px] font-extrabold rounded-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-all duration-200 shadow-lg whitespace-nowrap flex items-center gap-1 border border-slate-700/50">
            <span>Top</span>
            <span class="text-orange-400 font-black" x-text="scrollPercent + '%'"></span>
            <span>↑</span>
        </span>
    </button>
</div>
