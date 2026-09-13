import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

/**
 * `data-community` hanya di-set server-side pada initial page load
 * (resources/views/app.blade.php). Navigasi Inertia (<Link>) tidak me-render ulang
 * blade, sehingga tema komunitas akan tertinggal — mis. halaman login FAD tetap
 * memakai token `public`. Sinkronkan setiap kali props halaman berubah.
 */
function syncCommunityTheme(page) {
    const key = page?.props?.community?.key ?? 'public';
    if (document.documentElement.dataset.community !== key) {
        document.documentElement.dataset.community = key;
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
