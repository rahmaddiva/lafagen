<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Tags } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import Table from '@/components/ui/table/Table.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import {
    AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogOverlay, AlertDialogPortal, AlertDialogRoot, AlertDialogTitle,
    DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle,
} from 'radix-vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import { useCommunity } from '@/composables/useCommunity';
import { formatTanggal } from '@/lib/format';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const { url } = useCommunity();

const dialogOpen = ref(false);
const editing = ref(null);
const name = ref('');
const errorName = ref('');
const deleteTarget = ref(null);

// Validasi inline: pesan error hilang begitu pengguna memperbaiki isian.
watch(name, (value) => {
    if (value.trim() !== '') errorName.value = '';
});

function openCreate() {
    editing.value = null;
    name.value = '';
    errorName.value = '';
    dialogOpen.value = true;
}

function openEdit(category) {
    editing.value = category;
    name.value = category.name;
    errorName.value = '';
    dialogOpen.value = true;
}

function submit() {
    if (name.value.trim() === '') {
        errorName.value = 'Nama kategori wajib diisi.';
        return;
    }
    errorName.value = '';

    const payload = { name: name.value };
    if (editing.value) {
        payload._method = 'put';
        router.post(url(`categories/${editing.value.id}`), payload, { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; } });
    } else {
        router.post(url('categories'), payload, { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; name.value = ''; } });
    }
}

function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(url(`categories/${deleteTarget.value.id}`), { preserveScroll: true });
    deleteTarget.value = null;
}

const focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';
</script>

<template>
    <CommunityLayout title="Kategori">
        <PageHeader title="Kategori laporan">
            <template #actions>
                <Button
                    class="min-h-[44px] gap-2 font-semibold md:min-h-0"
                    :class="focusRing"
                    @click="openCreate"
                >
                    <Plus class="h-4 w-4" aria-hidden="true" />
                    Tambah Kategori
                </Button>
            </template>
        </PageHeader>

        <EmptyState
            v-if="!categories.length"
            title="Belum ada kategori"
            description="Buat kategori terlebih dahulu. Kategori wajib dipilih saat membuat laporan, jadi setidaknya harus ada satu kategori sebelum laporan bisa dibuat."
        >
            <template #icon>
                <Tags class="h-6 w-6" aria-hidden="true" />
            </template>
            <template #action>
                <Button class="mt-1 min-h-[44px] gap-2 md:min-h-0" :class="focusRing" @click="openCreate">
                    <Plus class="h-4 w-4" aria-hidden="true" />
                    Tambah Kategori
                </Button>
            </template>
        </EmptyState>

        <!-- Desktop: tabel -->
        <div v-else class="hidden overflow-hidden rounded-xl border bg-card md:block">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Kategori</TableHead>
                        <TableHead>Jumlah laporan</TableHead>
                        <TableHead>Dibuat</TableHead>
                        <TableHead class="text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="c in categories" :key="c.id">
                        <TableCell class="max-w-[28rem] min-w-0 font-semibold">
                            <span class="block truncate">{{ c.name }}</span>
                        </TableCell>
                        <TableCell>
                            <Badge variant="secondary">{{ c.reports_count }}</Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">
                            {{ formatTanggal(c.created_at) }}
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    :aria-label="`Ubah ${c.name}`"
                                    :class="focusRing"
                                    @click="openEdit(c)"
                                >
                                    <Pencil class="h-4 w-4" aria-hidden="true" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="text-destructive hover:text-destructive"
                                    :aria-label="`Hapus ${c.name}`"
                                    :class="focusRing"
                                    @click="deleteTarget = c"
                                >
                                    <Trash2 class="h-4 w-4" aria-hidden="true" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Mobile: kartu dari sumber data yang sama -->
        <div v-if="categories.length" class="space-y-3 md:hidden">
            <div
                v-for="c in categories"
                :key="c.id"
                class="rounded-xl border bg-card p-4 shadow-sm"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ c.name }}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Dibuat {{ formatTanggal(c.created_at) }}
                        </p>
                    </div>
                    <Badge variant="secondary" class="shrink-0">{{ c.reports_count }} laporan</Badge>
                </div>
                <div class="mt-3 flex gap-2">
                    <Button
                        variant="outline"
                        class="min-h-[44px] flex-1 gap-2"
                        :class="focusRing"
                        @click="openEdit(c)"
                    >
                        <Pencil class="h-4 w-4" aria-hidden="true" />
                        Ubah
                    </Button>
                    <Button
                        variant="outline"
                        class="min-h-[44px] flex-1 gap-2 text-destructive hover:text-destructive"
                        :class="focusRing"
                        @click="deleteTarget = c"
                    >
                        <Trash2 class="h-4 w-4" aria-hidden="true" />
                        Hapus
                    </Button>
                </div>
            </div>
        </div>

        <!-- Dialog tambah/ubah (satu dialog, judul berbeda sesuai mode) -->
        <DialogRoot v-model:open="dialogOpen">
            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                <DialogContent class="fixed left-1/2 top-1/2 z-50 w-full max-w-sm -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-6 shadow-lg">
                    <DialogTitle class="text-lg font-bold">
                        {{ editing ? 'Ubah kategori' : 'Tambah kategori' }}
                    </DialogTitle>
                    <DialogDescription id="cat-name-hint" class="mt-1 text-sm text-muted-foreground">
                        Nama kategori unik dalam komunitas ini.
                    </DialogDescription>
                    <div class="mt-4 space-y-1.5">
                        <Label for="cat-name">Nama</Label>
                        <Input
                            id="cat-name"
                            v-model="name"
                            maxlength="100"
                            :aria-invalid="errorName ? 'true' : undefined"
                            :aria-describedby="errorName ? 'cat-name-error' : 'cat-name-hint'"
                            :class="[
                                'h-10 focus-visible:ring-2 focus-visible:ring-ring',
                                errorName ? 'border-destructive' : '',
                            ]"
                            @keydown.enter="submit"
                        />
                        <p
                            v-if="errorName"
                            id="cat-name-error"
                            class="text-sm font-medium text-destructive"
                            role="alert"
                        >
                            {{ errorName }}
                        </p>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <Button variant="outline" class="min-h-[44px] md:min-h-0" :class="focusRing" @click="dialogOpen = false">
                            Batal
                        </Button>
                        <Button class="min-h-[44px] md:min-h-0" :class="focusRing" @click="submit">
                            Simpan
                        </Button>
                    </div>
                </DialogContent>
            </DialogPortal>
        </DialogRoot>

        <!-- Konfirmasi hapus -->
        <AlertDialogRoot :open="!!deleteTarget" @update:open="(v) => !v && (deleteTarget = null)">
            <AlertDialogPortal>
                <AlertDialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                <AlertDialogContent class="fixed left-1/2 top-1/2 z-50 w-full max-w-sm -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-6 shadow-lg">
                    <AlertDialogTitle class="text-lg font-bold">Hapus kategori?</AlertDialogTitle>
                    <AlertDialogDescription class="mt-1 text-sm text-muted-foreground">
                        Kategori “{{ deleteTarget?.name }}”
                        dengan {{ deleteTarget?.reports_count ?? 0 }} laporan akan dihapus.
                        <template v-if="(deleteTarget?.reports_count ?? 0) > 0">
                            Kategori ini masih dipakai oleh laporan tersebut, jadi penghapusan akan
                            ditolak. Pindahkan atau hapus laporannya lebih dulu.
                        </template>
                        <template v-else>
                            Tindakan ini tidak bisa dibatalkan.
                        </template>
                    </AlertDialogDescription>
                    <div class="mt-5 flex justify-end gap-2">
                        <AlertDialogCancel as-child>
                            <Button variant="outline" class="min-h-[44px] md:min-h-0" :class="focusRing">Batal</Button>
                        </AlertDialogCancel>
                        <AlertDialogAction as-child>
                            <Button variant="destructive" class="min-h-[44px] md:min-h-0" :class="focusRing" @click="confirmDelete">
                                Hapus
                            </Button>
                        </AlertDialogAction>
                    </div>
                </AlertDialogContent>
            </AlertDialogPortal>
        </AlertDialogRoot>
    </CommunityLayout>
</template>
