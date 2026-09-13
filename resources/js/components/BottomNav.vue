<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    FileText,
    PlusCircle,
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
 * Tombol "Tambah" selalu berada di tengah agar terjangkau jempol.
 * - anggota: Dashboard | Tambah | Laporan            (3 item)
 * - admin:   Dashboard | Laporan | Tambah | Kategori | Pengguna  (5 item)
 * Item admin disembunyikan untuk anggota — grid menyesuaikan, bukan sel kosong.
 *
 * Catatan: tidak ada halaman "Profil" di aplikasi ini (tidak ada route maupun
 * komponennya). Item keempat untuk anggota sempat ada tetapi menunjuk ke
 * dashboard — duplikat yang tidak pernah aktif — sehingga dihapus. Tambahkan
 * kembali hanya setelah halaman profilnya benar-benar ada.
 */
const items = computed(() => {
    const left = [
        { key: 'dashboard', label: 'Dashboard', icon: LayoutDashboard, href: url('dashboard') },
    ];
    const right = [
        { key: 'reports', label: 'Laporan', icon: FileText, href: url('reports') },
    ];

    if (role.value === 'admin') {
        left.push({ key: 'reports', label: 'Laporan', icon: FileText, href: url('reports') });
        right.length = 0;
        right.push(
            { key: 'categories', label: 'Kategori', icon: Tags, href: url('categories') },
            { key: 'users', label: 'Pengguna', icon: Users, href: url('users') },
        );
    }

    const fab = {
        key: 'create',
        label: 'Tambah',
        icon: PlusCircle,
        href: url('reports/create'),
        fab: true,
    };

    return [...left, fab, ...right];
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
            <li v-for="(item, i) in items" :key="item.key + '-' + i">
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
