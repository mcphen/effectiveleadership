/**
 * Interactions du site public : menu mobile, menus déroulants, carrousel de témoignages,
 * apparition au défilement, compteurs animés.
 */
import Alpine from 'alpinejs';

declare global {
    interface Window {
        Alpine: typeof Alpine;
    }
}

// En-tête : ombre et hauteur réduite après défilement.
Alpine.data('siteHeader', () => ({
    scrolled: false,
    mobileOpen: false,
    init() {
        const onScroll = () => (this.scrolled = window.scrollY > 24);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        this.$watch('mobileOpen', (open: boolean) => document.documentElement.classList.toggle('overflow-hidden', open));
    },
}));

// Carrousel simple (témoignages).
Alpine.data('slider', (count: number) => ({
    index: 0,
    count,
    timer: 0 as number,
    init() {
        this.play();
    },
    play() {
        if (this.count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        clearInterval(this.timer);
        this.timer = window.setInterval(() => this.next(), 7000);
    },
    stop() {
        clearInterval(this.timer);
    },
    next() {
        this.index = (this.index + 1) % this.count;
    },
    prev() {
        this.index = (this.index - 1 + this.count) % this.count;
    },
    go(i: number) {
        this.index = i;
        this.play();
    },
}));

// Compteur animé : « 15+ » compte de 0 à 15 quand il devient visible.
Alpine.data('counter', (raw: string) => ({
    display: raw,
    init() {
        const match = raw.match(/^(\D*)(\d[\d\s.,]*)(.*)$/);
        if (!match || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const [, prefix, number, suffix] = match;
        const target = parseInt(number.replace(/[\s.,]/g, ''), 10);
        if (!target) return;
        this.display = prefix + '0' + suffix;
        const observer = new IntersectionObserver((entries) => {
            if (!entries[0].isIntersecting) return;
            observer.disconnect();
            const start = performance.now();
            const step = (now: number) => {
                const p = Math.min((now - start) / 1400, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                this.display = prefix + Math.round(target * eased).toLocaleString(document.documentElement.lang) + suffix;
                if (p < 1) requestAnimationFrame(step);
                else this.display = raw;
            };
            requestAnimationFrame(step);
        });
        observer.observe(this.$el);
    },
}));

window.Alpine = Alpine;
Alpine.start();

// Apparition progressive des éléments .reveal
const revealObserver = new IntersectionObserver(
    (entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        }
    },
    { rootMargin: '0px 0px -8% 0px' },
);
document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));
