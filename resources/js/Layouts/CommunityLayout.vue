<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { toast, Toaster } from 'vue-sonner';
import { watch } from 'vue';
import { LayoutDashboard, FileText, Tags, Users, LogOut } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import BottomNav from '@/components/BottomNav.vue';
import NavItem from '@/components/NavItem.vue';
import { useCommunity } from '@/composables/useCommunity';

const props = defineProps({ title: { type: String, default: 'Lafagen' } });

const page = usePage();
const { community, url } = useCommunity();
const authUser = computed(() => page.props.auth.user);

const logoutForm = useForm({});

function logout() {
    logoutForm.post(url('logout'), { preserveScroll: true });
}

function current(path) {
    return page.url.startsWith(`/${community.value.key}/${path}`);
}

const navItems = computed(() => {
    const items = [
        { key: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, href: url('dashboard') },
        { key: 'reports', label: 'Laporan', icon: FileText, href: url('reports') },
    ];

    if (authUser.value.role === 'admin') {
        items.push(
            { key: 'categories', label: 'Kategori', icon: Tags, href: url('categories') },
            { key: 'users', label: 'Pengguna', icon: Users, href: url('users') },
        );
    }

    return items;
});

const initials = computed(() =>
    (authUser.value?.name ?? '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase() ?? '')
        .join(''),
);

const roleLabel = computed(() => (authUser.value.role === 'admin' ? 'Admin' : 'Anggota'));

const activeSection = computed(() => {
    const rest = page.url.slice(`/${community.value.key}/`.length);
    const seg = rest.split('/')[0] || 'dashboard';
    return seg === 'reports' && rest.includes('/create') ? 'create' : seg;
});

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    },
    { immediate: true },
);
</script>

<template>
    <div class="flex min-h-screen bg-background">
        <a
            href="#konten"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
        >
            Lewati ke konten
        </a>

        <aside class="hidden w-64 shrink-0 flex-col gap-2 border-r bg-card p-4 md:flex">
            <Link
                :href="url('dashboard')"
                class="flex items-center gap-3 rounded-lg px-2 py-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
            >
                <img
                    :src="community.logo"
                    :alt="community.short"
                    class="h-10 w-10 object-contain"
                    @error="$event.target.style.display = 'none'"
                >
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold leading-tight">
                        {{ community.title }}
                    </span>
                    <span class="block truncate text-xs text-muted-foreground">
                        {{ community.name }}
                    </span>
                </span>
            </Link>

            <Separator class="my-1" />

            <nav class="flex flex-col gap-1" aria-label="Navigasi utama">
                <NavItem
                    v-for="item in navItems"
                    :key="item.key"
                    :href="item.href"
                    :label="item.label"
                    :icon="item.icon"
                    :active="current(item.key)"
                />
            </nav>

            <div class="mt-auto">
                <Separator class="mb-3" />
                <div class="flex items-center gap-3 rounded-lg bg-muted/60 px-3 py-2">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground"
                        aria-hidden="true"
                    >
                        {{ initials }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold">{{ authUser.name }}</span>
                        <span class="block text-xs text-muted-foreground">{{ roleLabel }}</span>
                    </span>
                </div>
                <form class="mt-2" @submit.prevent="logout">
                    <Button
                        type="submit"
                        variant="outline"
                        class="w-full focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        :disabled="logoutForm.processing"
                    >
                        <LogOut class="mr-2 h-4 w-4" aria-hidden="true" />
                        Keluar
                    </Button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b bg-card px-4 py-3 md:px-6"
            >
                <h1 class="truncate text-lg font-bold tracking-tight">
                    {{ title }}
                </h1>

                <p class="hidden shrink-0 items-center gap-2 text-sm text-muted-foreground md:flex">
                    <span class="font-semibold text-foreground">{{ authUser.name }}</span>
                    <span
                        class="rounded-full bg-primary-soft px-2 py-0.5 text-xs font-semibold text-primary-strong"
                    >
                        {{ roleLabel }}
                    </span>
                </p>

                <div class="flex shrink-0 items-center gap-2 md:hidden">
                    <span class="truncate text-sm font-semibold">{{ authUser.name }}</span>
                    <Button
                        variant="ghost"
                        size="icon"
                        :disabled="logoutForm.processing"
                        aria-label="Keluar"
                        class="h-11 w-11 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    >
                        <LogOut class="h-5 w-5" aria-hidden="true" />
                    </Button>
                </div>
            </header>

            <main id="konten" class="mx-auto w-full max-w-6xl flex-1 p-4 pb-24 md:p-6 md:pb-6">
                <slot />
            </main>
        </div>

        <BottomNav :active="activeSection" />
        <Toaster rich-colors position="top-center" />
    </div>
</template>
