import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

/**
 * `data-community` dan favicon hanya di-set server-side pada initial page load
 * (resources/views/app.blade.php). Navigasi Inertia (<Link>) tidak me-render ulang
 * blade, sehingga keduanya tertinggal — mis. login GENRE lewat SPA tetap memakai
 * favicon FAD, dan halaman landing ikut komunitas terakhir. Sinkronkan setiap kali
 * props halaman berubah.
 */
const FAVICON = {
    fad: '/images/fad.png',
    genre: '/images/genre.png',
    public: '/favicon.png',
};

function syncCommunityTheme(page) {
    const key = page?.props?.community?.key ?? 'public';
    if (document.documentElement.dataset.community !== key) {
        document.documentElement.dataset.community = key;
    }

    const icon = document.getElementById('favicon');
    if (icon) {
        const href = FAVICON[key] ?? FAVICON.public;
        if (icon.getAttribute('href') !== href) icon.setAttribute('href', href);
    }
}

createInertiaApp({
    title: (title) => (title ? `${title} — Lafagen` : 'Lafagen'),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        syncCommunityTheme(props.initialPage);
        router.on('navigate', (event) => syncCommunityTheme(event.detail.page));

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: 'hsl(var(--primary))',
    },
});
