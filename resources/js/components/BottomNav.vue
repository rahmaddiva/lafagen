<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    FileText,
    PlusCircle,
    User,
    Tags,
    Users,
} from 'lucide-vue-next';
import NavItem from '@/components/NavItem.vue';
import { useCommunity } from '@/composables/useCommunity';

const props = defineProps({
    active: { type: String, default: '' },
});

const { url } = useCommunity();
const page = usePage();
const role = computed(() => page.props.auth?.user?.role);

/**
 * Anggota: 4 item (Dashboard, Laporan, Tambah, Profil).
 * Admin: 5 item (tambah Kategori & Pengguna).
 * Item admin disembunyikan untuk anggota — grid menyesuaikan, bukan sel kosong.
 */
const items = computed(() => {
    const base = [
        { key: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, href: url('dashboard') },
        { key: 'reports', label: 'Laporan', icon: FileText, href: url('reports') },
        { key: 'create', label: 'Tambah', icon: PlusCircle, href: url('reports/create'), fab: true },
    ];

    if (role.value === 'admin') {
        base.push(
            { key: 'categories', label: 'Kategori', icon: Tags, href: url('categories') },
            { key: 'users', label: 'Pengguna', icon: Users, href: url('users') },
        );
    } else {
        base.push({ key: 'profile', label: 'Profil', icon: User, href: url('dashboard') });
    }

    return base;
});

function isActive(key) {
    return props.active === key;
}
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-20 border-t bg-card pb-[env(safe-area-inset-bottom)] md:hidden"
        aria-label="Navigasi utama"
    >
        <ul
            class="mx-auto grid max-w-lg items-end gap-1 px-2 py-1"
            :style="{ gridTemplateColumns: `repeat(${items.length}, minmax(0, 1fr))` }"
        >
            <li v-for="item in items" :key="item.key">
                <NavItem
                    :href="item.href"
                    :label="item.label"
                    :icon="item.icon"
                    :active="isActive(item.key)"
                    :variant="item.fab ? 'fab' : 'bottom'"
                />
            </li>
        </ul>
    </nav>
</template>
