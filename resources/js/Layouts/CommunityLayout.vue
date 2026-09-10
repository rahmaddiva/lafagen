<script setup>
import { computed } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { toast, Toaster } from 'vue-sonner';
import { watch } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import Separator from '@/components/ui/separator/Separator.vue';
import { useCommunity } from '@/composables/useCommunity';

defineProps({ title: { type: String, default: 'Lafagen' } });

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
        <aside class="hidden w-64 shrink-0 flex-col gap-2 border-r bg-card p-4 md:flex">
            <Link :href="url('dashboard')" class="flex items-center gap-3 rounded-md px-2 py-3">
                <img
                    :src="community.logo"
                    :alt="community.short"
                    class="h-10 w-10 object-contain"
                    @error="$event.target.style.display = 'none'"
                >
                <span>
                    <span class="block text-sm font-bold leading-tight">{{ community.title }}</span>
                    <span class="block text-xs text-muted-foreground">{{ community.name }}</span>
                </span>
            </Link>
            <Separator />
            <Link :href="url('dashboard')" class="nav-link" :class="{ 'nav-link-active': current('dashboard') }">
                Dashboard
            </Link>
            <Link :href="url('reports')" class="nav-link" :class="{ 'nav-link-active': current('reports') }">
                Laporan
            </Link>
            <template v-if="authUser.role === 'admin'">
                <Link :href="url('categories')" class="nav-link" :class="{ 'nav-link-active': current('categories') }">
                    Kategori
                </Link>
                <Link :href="url('users')" class="nav-link" :class="{ 'nav-link-active': current('users') }">
                    Pengguna
                </Link>
            </template>
            <div class="mt-auto">
                <Separator class="mb-3" />
                <p class="px-2 text-xs text-muted-foreground">
                    {{ authUser.name }} · {{ authUser.role === 'admin' ? 'Admin' : 'Anggota' }}
                </p>
                <form class="mt-2" @submit.prevent="logout">
                    <Button type="submit" variant="outline" class="w-full" :disabled="logoutForm.processing">
                        Keluar
                    </Button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b bg-card px-4 py-3 md:px-6">
                <h1 class="truncate text-lg font-bold">
                    {{ title }}
                </h1>
                <nav class="flex items-center gap-2 md:hidden">
                    <Link :href="url('dashboard')" class="text-sm text-primary">
                        Dashboard
                    </Link>
                    <span class="text-muted-foreground">·</span>
                    <Link :href="url('reports')" class="text-sm text-primary">
                        Laporan
                    </Link>
                    <button class="text-sm text-destructive" @click="logout">
                        Keluar
                    </button>
                </nav>
                <p class="hidden text-sm text-muted-foreground md:block">
                    {{ authUser.name }} ({{ authUser.role === 'admin' ? 'Admin' : 'Anggota' }})
                </p>
            </header>
            <main class="mx-auto w-full max-w-6xl flex-1 p-4 md:p-6">
                <slot />
            </main>
        </div>
        <Toaster rich-colors position="top-center" />
    </div>
</template>
